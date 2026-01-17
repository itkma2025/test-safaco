<?php  
function createSimpleXlsx($filename, $headers, $data, $alignments = []) {
    $zip = new ZipArchive();
    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        die("Tidak bisa membuka $filename");
    }

    // Hitung panjang maksimal per kolom
    $colWidths = array_fill(0, count($headers), 0);

    foreach ($headers as $i => $header) {
        $colWidths[$i] = max($colWidths[$i], strlen($header));
    }

    foreach ($data as $row) {
        foreach ($row as $i => $cell) {
            $len = strlen($cell);
            // Jika ada email (mengandung @) tambahkan bobot ekstra
            if (strpos($cell, '@') !== false) {
                $len = $len * 1.3; // email lebih longgar
            }
            $colWidths[$i] = max($colWidths[$i], $len);
        }
    }

    // Konversi panjang string ke lebar kolom Excel
    foreach ($colWidths as &$w) {
        $w = ($w * 1.2) + 2; // faktor umum
    }



    // .rels
    $zip->addFromString("_rels/.rels", '<?xml version="1.0" encoding="UTF-8"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
            <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
        </Relationships>');

    // workbook.xml
    $zip->addFromString("xl/workbook.xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
        xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
            <sheets>
                <sheet name="Data" sheetId="1" r:id="rId1"/>
            </sheets>
        </workbook>');

    // sheet1.xml
    $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

    $worksheet .= '<cols>';
    foreach ($colWidths as $i => $w) {
        $colId = $i + 1;
        $worksheet .= '<col min="' . $colId . '" max="' . $colId . '" width="' . $w . '" customWidth="1"/>';
    }
    $worksheet .= '</cols>';

    $worksheet .= '<sheetData>';

    // header row pakai style s="1"
    $worksheet .= '<row>';
    foreach ($headers as $header) {
        $safe = htmlspecialchars($header, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $worksheet .= '<c t="inlineStr" s="1"><is><t>' . $safe . '</t></is></c>';
    }
    $worksheet .= '</row>';

    // data row (cek alignment)
    foreach ($data as $row) {
        $worksheet .= '<row>';
        foreach ($row as $i => $cell) {
            $safe = htmlspecialchars($cell ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $styleIndex = 0; // default kiri
            if (isset($alignments[$i])) {
                if ($alignments[$i] === 'center') $styleIndex = 2;
                elseif ($alignments[$i] === 'right') $styleIndex = 3;
            }

            $worksheet .= '<c t="inlineStr" s="' . $styleIndex . '"><is><t>' . $safe . '</t></is></c>';
        }
        $worksheet .= '</row>';
    }

    $worksheet .= '</sheetData></worksheet>';
    $zip->addFromString("xl/worksheets/sheet1.xml", $worksheet);

    // workbook.xml.rels
    $zip->addFromString("xl/_rels/workbook.xml.rels", '<?xml version="1.0" encoding="UTF-8"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
            <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
            <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
        </Relationships>');

    // styles.xml (0=left, 1=header, 2=center, 3=right)
    $zip->addFromString("xl/styles.xml", '<?xml version="1.0" encoding="UTF-8"?>
        <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
            <fonts count="3">
                <!-- font 0: normal isi -->
                <font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>
                <!-- font 1: header besar -->
                <font><b/><sz val="12"/><color theme="1"/><name val="Calibri"/></font>
                <!-- font 2: normal isi tapi bisa dipakai untuk variasi -->
                <font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>
            </fonts>
            <fills count="2">
                <fill><patternFill patternType="none"/></fill>
                <fill><patternFill patternType="solid"><fgColor rgb="FF000080"/><bgColor indexed="64"/></patternFill></fill>
            </fills>
            <borders count="1"><border/></borders>
            <cellStyleXfs count="1">
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
            </cellStyleXfs>
            <cellXfs count="4">
                <!-- 0: isi kiri -->
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
                    <alignment horizontal="left" vertical="center"/>
                </xf>
                <!-- 1: header (bold, abu, center, font besar) -->
                <xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0"
                    applyFont="1" applyFill="1" applyAlignment="1">
                    <alignment horizontal="center" vertical="center"/>
                </xf>
                <!-- 2: isi tengah -->
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
                    <alignment horizontal="center" vertical="center"/>
                </xf>
                <!-- 3: isi kanan -->
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
                    <alignment horizontal="right" vertical="center"/>
                </xf>
            </cellXfs>
        </styleSheet>');
    // [Content_Types].xml
    $zip->addFromString("[Content_Types].xml", '<?xml version="1.0" encoding="UTF-8"?>
        <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
            <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
            <Default Extension="xml" ContentType="application/xml"/>
            <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
            <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
            <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
        </Types>');

    $zip->close();
}
?>