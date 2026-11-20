<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class XlsxExportService
{
    /**
     * Daftar style cellXfs yang didaftarkan secara dinamis selama pembuatan sheet.
     *
     * @var array<int, array{fontId: int, fillId: int, borderId: int, hAlign: string, vAlign: string, wrap: bool}>
     */
    private array $cellStyles = [];

    /**
     * Peta lookup signature style -> index integer pada styles.xml.
     *
     * @var array<string, int>
     */
    private array $styleIndexMap = [];

    /**
     * Menghasilkan konten biner berkas .xlsx (OOXML) yang valid dengan lebar kolom dan tinggi baris otomatis.
     *
     * @param  array<string, array{label: string, group?: string, align?: string}>  $activeColumns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $summaryData
     * @param  array<string, mixed>  $requestParams
     */
    public function generate(
        array $activeColumns,
        array $rows,
        bool $includeSummary,
        array $summaryData,
        array $requestParams,
        string $exportTime
    ): string {
        $this->cellStyles = [];
        $this->styleIndexMap = [];

        // Index 0 selalu default style
        $this->registerStyle(0, 0, 0, 'left', 'center', false);

        $layout = $this->computeColumnLayout($activeColumns, $rows, $includeSummary, $summaryData);
        $sheetXml = $this->buildSheetXml(
            $activeColumns,
            $rows,
            $includeSummary,
            $summaryData,
            $requestParams,
            $exportTime,
            $layout
        );
        $stylesXml = $this->buildStylesXml();

        $tempPath = tempnam(sys_get_temp_dir(), 'whms_xlsx_');
        if ($tempPath === false) {
            throw new RuntimeException('Gagal membuat berkas sementara untuk ekspor XLSX.');
        }

        $zip = new ZipArchive;
        if ($zip->open($tempPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tempPath);
            throw new RuntimeException('Gagal membuka arsip ZIP untuk berkas XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml());
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = file_get_contents($tempPath);
        @unlink($tempPath);

        if ($binary === false) {
            throw new RuntimeException('Gagal membaca hasil biner ekspor XLSX.');
        }

        return $binary;
    }

    /**
     * Menghitung lebar kolom Excel (dalam satuan karakter OOXML) dan tinggi baris (pt).
     *
     * @param  array<string, array{label: string, group?: string, align?: string}>  $activeColumns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $summaryData
     * @return array{
     *     widths_char: array<string, float>,
     *     wrap_cols: array<string, bool>,
     *     row_heights_pt: array<int, float>
     * }
     */
    public function computeColumnLayout(array $activeColumns, array $rows, bool $includeSummary, array $summaryData): array
    {
        $maxCharWidth = 56.0;
        $minCharWidth = 7.5;
        $widthsChar = [];
        $wrapCols = [];
        $firstColKey = array_key_first($activeColumns);

        foreach ($activeColumns as $colKey => $col) {
            $headerLabel = trim((string) ($col['label'] ?? $colKey));
            $requiredWidth = max($minCharWidth, round((mb_strlen($headerLabel) * 1.18) + 4.5, 2));

            foreach ($rows as $row) {
                $rawValue = (string) ($row[$colKey] ?? '-');
                $lines = preg_split('/\r\n|\r|\n/', $rawValue) ?: [$rawValue];
                foreach ($lines as $line) {
                    $lineLen = mb_strlen(trim((string) $line));
                    $candidateWidth = round(($lineLen * 1.10) + 4.0, 2);
                    if ($candidateWidth > $requiredWidth) {
                        $requiredWidth = $candidateWidth;
                    }
                }
            }

            if ($includeSummary) {
                $summaryText = $this->resolveSummaryText($colKey, $firstColKey, $summaryData);
                if ($summaryText !== '') {
                    $summaryWidth = round((mb_strlen($summaryText) * 1.15) + 4.0, 2);
                    if ($summaryWidth > $requiredWidth) {
                        $requiredWidth = $summaryWidth;
                    }
                }
            }

            $shouldWrap = $requiredWidth > $maxCharWidth;
            $widthsChar[$colKey] = $shouldWrap ? $maxCharWidth : $requiredWidth;
            $wrapCols[$colKey] = $shouldWrap;
        }

        $rowHeightsPt = [];
        foreach ($rows as $idx => $row) {
            $maxLines = 1;
            foreach ($activeColumns as $colKey => $col) {
                $rawValue = trim((string) ($row[$colKey] ?? '-'));
                if ($rawValue === '') {
                    continue;
                }

                $explicitLines = preg_split('/\r\n|\r|\n/', $rawValue) ?: [$rawValue];
                $colLineCount = 0;
                $charsPerLine = ($wrapCols[$colKey] ?? false) ? 46 : PHP_INT_MAX;

                foreach ($explicitLines as $line) {
                    $lineLen = max(1, mb_strlen(trim((string) $line)));
                    $colLineCount += ($charsPerLine === PHP_INT_MAX) ? 1 : (int) ceil($lineLen / $charsPerLine);
                }

                if ($colLineCount > $maxLines) {
                    $maxLines = $colLineCount;
                }
            }

            $rowHeightsPt[$idx] = (float) max(22, ($maxLines * 15) + 8);
        }

        return [
            'widths_char' => $widthsChar,
            'wrap_cols' => $wrapCols,
            'row_heights_pt' => $rowHeightsPt,
        ];
    }

    /**
     * Membangun XML lengkap worksheet (xl/worksheets/sheet1.xml).
     *
     * @param  array<string, array{label: string, group?: string, align?: string}>  $activeColumns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $summaryData
     * @param  array<string, mixed>  $requestParams
     * @param  array{widths_char: array<string, float>, wrap_cols: array<string, bool>, row_heights_pt: array<int, float>}  $layout
     */
    private function buildSheetXml(
        array $activeColumns,
        array $rows,
        bool $includeSummary,
        array $summaryData,
        array $requestParams,
        string $exportTime,
        array $layout
    ): string {
        $colKeys = array_keys($activeColumns);
        $colCount = max(1, count($colKeys));
        $lastColLetter = $this->columnIndexToLetter($colCount);

        $titleStyle = $this->registerStyle(1, 0, 0, 'left', 'center', false);
        $docTitleStyle = $this->registerStyle(2, 0, 0, 'left', 'center', false);
        $subNoteStyle = $this->registerStyle(3, 0, 0, 'left', 'center', false);
        $metaStyle = $this->registerStyle(0, 0, 0, 'left', 'center', false);
        $metaRightStyle = $this->registerStyle(0, 0, 0, 'right', 'center', false);
        $headerStyle = $this->registerStyle(4, 2, 1, 'center', 'center', true);

        $sheetRows = [];
        $merges = [];
        $rowNum = 1;

        // 1. Kop & Judul Resmi
        $sheetRows[] = $this->buildSingleMergedTextRow(
            $rowNum,
            $lastColLetter,
            'PT ANGKASA PURA INDONESIA — INJOURNEY AIRPORTS',
            $titleStyle,
            22.0,
            $merges
        );
        $rowNum++;

        $sheetRows[] = $this->buildSingleMergedTextRow(
            $rowNum,
            $lastColLetter,
            'REKAPITULASI LOGBOOK PENGAMATAN & PENGENDALIAN BAHAYA SATWA LIAR (WHMS)',
            $docTitleStyle,
            24.0,
            $merges
        );
        $rowNum++;

        $sheetRows[] = $this->buildSingleMergedTextRow(
            $rowNum,
            $lastColLetter,
            'Standar Kepatuhan: ICAO Annex 14 Bab 9 • ICAO Doc 9137 Part 3 • Formulir Baku Ditjen Perhubungan Udara (DKPPU)',
            $subNoteStyle,
            18.0,
            $merges
        );
        $rowNum++;

        // 2. Metadata Baris 1 & 2 (di-merge proporsional agar kolom A tidak melebar paksa)
        $modeText = ($requestParams['mode_baris'] ?? '') === 'per_satwa'
            ? 'Rincian Per Temuan Satwa (Standar DKPPU)'
            : 'Ringkasan Per Laporan';

        $sheetRows[] = $this->buildTwoSpanMetadataRow(
            $rowNum,
            $colCount,
            'Waktu Ekspor: '.$exportTime,
            'Total Laporan Terkait: '.($summaryData['total_laporan'] ?? 0).' Laporan',
            $metaStyle,
            $merges
        );
        $rowNum++;

        $sheetRows[] = $this->buildTwoSpanMetadataRow(
            $rowNum,
            $colCount,
            'Mode Tampilan Baris: '.$modeText,
            'Tingkat Penyelesaian: '.($summaryData['persen_selesai'] ?? 0).'% Telah Ditangani',
            $metaStyle,
            $merges
        );
        $rowNum++;

        if (! empty($requestParams['unit_kerja']) && $requestParams['unit_kerja'] !== 'all') {
            $sheetRows[] = $this->buildSingleMergedTextRow(
                $rowNum,
                $lastColLetter,
                'Filter Unit Kerja: '.$requestParams['unit_kerja'],
                $metaStyle,
                18.0,
                $merges
            );
            $rowNum++;
        }

        // Baris pemisah kosong
        $rowNum++;

        // 3. Header Tabel Data
        $headerCellsXml = '';
        foreach ($colKeys as $i => $colKey) {
            $colRef = $this->columnIndexToLetter($i + 1).$rowNum;
            $label = (string) ($activeColumns[$colKey]['label'] ?? $colKey);
            $headerCellsXml .= $this->buildInlineStringCell($colRef, $headerStyle, $label);
        }
        $sheetRows[] = "<row r=\"{$rowNum}\" ht=\"26\" customHeight=\"1\">{$headerCellsXml}</row>";
        $rowNum++;

        // 4. Baris Data
        if (empty($rows)) {
            $emptyStyle = $this->registerStyle(3, 4, 1, 'center', 'center', false);
            $sheetRows[] = $this->buildSingleMergedTextRow(
                $rowNum,
                $lastColLetter,
                'Tidak ada rekaman data logbook satwa liar yang sesuai dengan kriteria filter terpilih.',
                $emptyStyle,
                26.0,
                $merges,
                true
            );
            $rowNum++;
        } else {
            foreach ($rows as $idx => $r) {
                $fillId = ($idx % 2 === 0) ? 3 : 4;
                $rowHeightPt = $layout['row_heights_pt'][$idx] ?? 22.0;
                $cellsXml = '';

                foreach ($colKeys as $i => $colKey) {
                    $colRef = $this->columnIndexToLetter($i + 1).$rowNum;
                    $val = $r[$colKey] ?? '-';
                    $align = (string) ($activeColumns[$colKey]['align'] ?? 'left');
                    $shouldWrap = (bool) ($layout['wrap_cols'][$colKey] ?? false) || str_contains((string) $val, "\n");

                    $fontId = 0;
                    if ($colKey === 'status_penanganan') {
                        $fontId = str_contains(strtolower((string) $val), 'telah') ? 6 : 7;
                    } elseif ($colKey === 'jumlah_ekor') {
                        $fontId = 8;
                    }

                    $styleId = $this->registerStyle($fontId, $fillId, 1, $align, 'center', $shouldWrap);

                    if (($colKey === 'no' || $colKey === 'jumlah_ekor') && is_numeric($val) && ! str_starts_with((string) $val, '0')) {
                        $cellsXml .= $this->buildNumericCell($colRef, $styleId, (string) $val);
                    } else {
                        $cellsXml .= $this->buildInlineStringCell($colRef, $styleId, (string) $val);
                    }
                }

                $sheetRows[] = "<row r=\"{$rowNum}\" ht=\"{$rowHeightPt}\" customHeight=\"1\">{$cellsXml}</row>";
                $rowNum++;
            }

            // 5. Baris Summary Total
            if ($includeSummary) {
                $firstColKey = $colKeys[0] ?? null;
                $summaryCellsXml = '';

                foreach ($colKeys as $i => $colKey) {
                    $colRef = $this->columnIndexToLetter($i + 1).$rowNum;
                    $summaryText = $this->resolveSummaryText($colKey, $firstColKey, $summaryData);
                    $align = match ($colKey) {
                        'jumlah_ekor' => 'right',
                        'no', 'id_laporan', 'amunisi_terpakai', 'status_penanganan' => 'center',
                        default => ($colKey === $firstColKey ? 'center' : 'left'),
                    };

                    if ($summaryText === '' && ($colKey === 'no' || $colKey === 'id_laporan')) {
                        $summaryText = '-';
                    }

                    $styleId = $this->registerStyle(5, 5, 2, $align, 'center', false);
                    $summaryCellsXml .= $this->buildInlineStringCell($colRef, $styleId, $summaryText);
                }

                $sheetRows[] = "<row r=\"{$rowNum}\" ht=\"24\" customHeight=\"1\">{$summaryCellsXml}</row>";
                $rowNum++;
            }
        }

        // 6. Lembar Tanda Tangan Validasi di Bagian Bawah
        $rowNum++;
        $sheetRows[] = $this->buildTwoSpanMetadataRow(
            $rowNum,
            $colCount,
            'Diterbitkan secara elektronik oleh: Sistem Logbook Bahaya Satwa Liar (WHMS) — PT Angkasa Pura Indonesia',
            'Mengetahui / Memvalidasi: Airside Safety & Wildlife Hazard Lead (Safety Management Unit)',
            $subNoteStyle,
            $merges,
            $metaRightStyle
        );
        $lastRowNum = $rowNum;

        // Kolom Widths (<cols>)
        $colsXml = '<cols>';
        foreach ($colKeys as $i => $colKey) {
            $colNumber = $i + 1;
            $widthChar = $layout['widths_char'][$colKey] ?? 16.0;
            $colsXml .= "<col min=\"{$colNumber}\" max=\"{$colNumber}\" width=\"{$widthChar}\" customWidth=\"1\"/>";
        }
        $colsXml .= '</cols>';

        $mergesXml = '';
        if (! empty($merges)) {
            $mergesXml = '<mergeCells count="'.count($merges).'">';
            foreach ($merges as $ref) {
                $mergesXml .= "<mergeCell ref=\"{$ref}\"/>";
            }
            $mergesXml .= '</mergeCells>';
        }

        $rowsXml = implode('', $sheetRows);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            ."<dimension ref=\"A1:{$lastColLetter}{$lastRowNum}\"/>"
            .'<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/>'
            .$colsXml
            ."<sheetData>{$rowsXml}</sheetData>"
            .$mergesXml
            .'<pageMargins left="0.5" right="0.5" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            .'</worksheet>';
    }

    /**
     * Membuat baris teks yang di-merge sepanjang seluruh kolom tabel.
     *
     * @param  array<int, string>  $merges
     */
    private function buildSingleMergedTextRow(
        int $rowNum,
        string $lastColLetter,
        string $text,
        int $styleId,
        float $heightPt,
        array &$merges,
        bool $fillMergedBorder = false
    ): string {
        if ($lastColLetter !== 'A') {
            $merges[] = "A{$rowNum}:{$lastColLetter}{$rowNum}";
        }

        $cells = $this->buildInlineStringCell("A{$rowNum}", $styleId, $text);
        if ($fillMergedBorder && $lastColLetter !== 'A') {
            $colIndex = 2;
            while ($this->columnIndexToLetter($colIndex) !== $lastColLetter) {
                $ref = $this->columnIndexToLetter($colIndex).$rowNum;
                $cells .= "<c r=\"{$ref}\" s=\"{$styleId}\"/>";
                $colIndex++;
            }
            $cells .= "<c r=\"{$lastColLetter}{$rowNum}\" s=\"{$styleId}\"/>";
        }

        return "<row r=\"{$rowNum}\" ht=\"{$heightPt}\" customHeight=\"1\">{$cells}</row>";
    }

    /**
     * Membuat baris dengan 2 kelompok kolom ter-merge kiri dan kanan agar tidak mengganggu lebar kolom individu.
     *
     * @param  array<int, string>  $merges
     */
    private function buildTwoSpanMetadataRow(
        int $rowNum,
        int $colCount,
        string $leftText,
        string $rightText,
        int $leftStyleId,
        array &$merges,
        ?int $rightStyleId = null
    ): string {
        $resolvedRightStyle = $rightStyleId ?? $leftStyleId;
        if ($colCount === 1) {
            return "<row r=\"{$rowNum}\" ht=\"18\" customHeight=\"1\">"
                .$this->buildInlineStringCell("A{$rowNum}", $leftStyleId, $leftText.' | '.$rightText)
                .'</row>';
        }

        $splitCol = max(1, (int) floor($colCount / 2));
        $leftEndLetter = $this->columnIndexToLetter($splitCol);
        $rightStartCol = $splitCol + 1;
        $rightStartLetter = $this->columnIndexToLetter($rightStartCol);
        $lastColLetter = $this->columnIndexToLetter($colCount);

        if ($splitCol > 1) {
            $merges[] = "A{$rowNum}:{$leftEndLetter}{$rowNum}";
        }
        if ($rightStartCol < $colCount) {
            $merges[] = "{$rightStartLetter}{$rowNum}:{$lastColLetter}{$rowNum}";
        }

        $cells = $this->buildInlineStringCell("A{$rowNum}", $leftStyleId, $leftText);
        $cells .= $this->buildInlineStringCell("{$rightStartLetter}{$rowNum}", $resolvedRightStyle, $rightText);

        return "<row r=\"{$rowNum}\" ht=\"18\" customHeight=\"1\">{$cells}</row>";
    }

    /**
     * Membangun cell inline string XML dengan karakter XML yang aman.
     */
    private function buildInlineStringCell(string $ref, int $styleId, string $value): string
    {
        $clean = $this->sanitizeXmlString($value);
        $escaped = htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return "<c r=\"{$ref}\" s=\"{$styleId}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>";
    }

    /**
     * Membangun cell angka XML.
     */
    private function buildNumericCell(string $ref, int $styleId, string $value): string
    {
        return "<c r=\"{$ref}\" s=\"{$styleId}\" t=\"n\"><v>{$value}</v></c>";
    }

    /**
     * Membuang karakter kontrol ilegal menurut spesifikasi XML 1.0.
     */
    private function sanitizeXmlString(string $value): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    }

    /**
     * Mendaftarkan style kombinasi font, fill, border, dan alignment, lalu mengembalikan index integer-nya.
     */
    private function registerStyle(
        int $fontId,
        int $fillId,
        int $borderId,
        string $hAlign,
        string $vAlign,
        bool $wrap
    ): int {
        $normalizedAlign = in_array($hAlign, ['left', 'center', 'right'], true) ? $hAlign : 'left';
        $key = "{$fontId}:{$fillId}:{$borderId}:{$normalizedAlign}:{$vAlign}:".($wrap ? '1' : '0');

        if (isset($this->styleIndexMap[$key])) {
            return $this->styleIndexMap[$key];
        }

        $index = count($this->cellStyles);
        $this->cellStyles[] = [
            'fontId' => $fontId,
            'fillId' => $fillId,
            'borderId' => $borderId,
            'hAlign' => $normalizedAlign,
            'vAlign' => $vAlign,
            'wrap' => $wrap,
        ];
        $this->styleIndexMap[$key] = $index;

        return $index;
    }

    /**
     * Menentukan teks ringkasan pada baris TOTAL untuk setiap kolom.
     *
     * @param  array<string, mixed>  $summaryData
     */
    private function resolveSummaryText(string $colKey, ?string $firstColKey, array $summaryData): string
    {
        if ($colKey === $firstColKey) {
            return 'TOTAL';
        }

        return match ($colKey) {
            'jumlah_ekor' => (string) ($summaryData['total_ekor'] ?? 0).' Ekor',
            'amunisi_terpakai' => (string) ($summaryData['total_amunisi'] ?? 0).' Butir',
            'status_penanganan' => (string) ($summaryData['persen_selesai'] ?? 0).'% Selesai',
            default => '',
        };
    }

    /**
     * Mengonversi nomor kolom 1-based menjadi huruf kolom Excel (1 -> A, 27 -> AA).
     */
    private function columnIndexToLetter(int $index): string
    {
        $letter = '';
        $num = $index;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $num = intdiv($num - 1, 26);
        }

        return $letter;
    }

    /**
     * Membangun xl/styles.xml dengan palet warna InJourney Airports dan seluruh xf yang terdaftar.
     */
    private function buildStylesXml(): string
    {
        $fontsXml = '<fonts count="9">'
            .'<font><sz val="10"/><color rgb="FF1E293B"/><name val="Arial"/></font>'
            .'<font><b/><sz val="13"/><color rgb="FF0F172A"/><name val="Arial"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FF006874"/><name val="Arial"/></font>'
            .'<font><sz val="9"/><color rgb="FF64748B"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF00363D"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF047857"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFB91C1C"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF1E293B"/><name val="Arial"/></font>'
            .'</fonts>';

        // Index 0 (none) dan 1 (gray125) wajib ada menurut spesifikasi Excel OOXML
        $fillsXml = '<fills count="6">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF00A9C1"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFD1F2F5"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>';

        $bordersXml = '<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border>'
            .'<left style="thin"><color rgb="FF94A3B8"/></left>'
            .'<right style="thin"><color rgb="FF94A3B8"/></right>'
            .'<top style="thin"><color rgb="FF94A3B8"/></top>'
            .'<bottom style="thin"><color rgb="FF94A3B8"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'<border>'
            .'<left style="thin"><color rgb="FF94A3B8"/></left>'
            .'<right style="thin"><color rgb="FF94A3B8"/></right>'
            .'<top style="medium"><color rgb="FF006874"/></top>'
            .'<bottom style="medium"><color rgb="FF006874"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'</borders>';

        $xfsXml = '<cellXfs count="'.count($this->cellStyles).'">';
        foreach ($this->cellStyles as $style) {
            $wrapAttr = $style['wrap'] ? ' wrapText="1"' : '';
            $xfsXml .= '<xf numFmtId="0" fontId="'.$style['fontId'].'" fillId="'.$style['fillId'].'" borderId="'.$style['borderId'].'" xfId="0"'
                .' applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
                .'<alignment horizontal="'.$style['hAlign'].'" vertical="'.$style['vAlign'].'"'.$wrapAttr.'/>'
                .'</xf>';
        }
        $xfsXml .= '</cellXfs>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$fontsXml
            .$fillsXml
            .$bordersXml
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .$xfsXml
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function buildContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Rekapitulasi Satwa Liar" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
    }
}
