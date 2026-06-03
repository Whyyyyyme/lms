<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class FileTextExtractor
{
    /**
     * Batas maksimal teks yang disimpan ke database.
     * Ini supaya kolom extracted_text tidak terlalu besar dan prompt AI tidak terlalu panjang.
     */
    private int $maxLength = 30000;

    /**
     * Membaca isi file dari storage Laravel.
     *
     * Contoh path:
     * - assignments/namafile.pdf
     * - materials/materi-cloud.pdf
     */
    public function extractFromStoragePath(?string $storagePath, string $disk = 'public'): ?string
    {
        if (blank($storagePath)) {
            return null;
        }

        try {
            if (! Storage::disk($disk)->exists($storagePath)) {
                return null;
            }

            $absolutePath = Storage::disk($disk)->path($storagePath);

            return $this->extractFromAbsolutePath($absolutePath);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Membaca isi file dari path asli di komputer/server.
     */
    public function extractFromAbsolutePath(?string $absolutePath): ?string
    {
        if (blank($absolutePath) || ! is_file($absolutePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        try {
            $text = match ($extension) {
                'pdf' => $this->extractPdf($absolutePath),
                'docx' => $this->extractDocx($absolutePath),
                'txt', 'md', 'csv' => $this->extractPlainText($absolutePath),
                default => null,
            };

            return $this->cleanText($text);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Membaca teks dari PDF.
     *
     * Catatan:
     * PDF hasil scan gambar biasanya tidak terbaca karena butuh OCR.
     */
    private function extractPdf(string $absolutePath): ?string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($absolutePath);

        return $pdf->getText();
    }

    /**
     * Membaca teks dari DOCX.
     */
    private function extractDocx(string $absolutePath): ?string
    {
        $phpWord = IOFactory::load($absolutePath);
        $texts = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $texts[] = $this->extractElementText($element);
            }
        }

        return implode("\n", array_filter($texts));
    }

    /**
     * Membaca teks dari TXT/MD/CSV.
     */
    private function extractPlainText(string $absolutePath): ?string
    {
        $text = file_get_contents($absolutePath);

        return $text !== false ? $text : null;
    }

    /**
     * Membaca teks dari elemen DOCX secara rekursif.
     */
    private function extractElementText(AbstractElement $element): string
    {
        $texts = [];

        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            if (is_string($text)) {
                $texts[] = $text;
            }
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $childElement) {
                if ($childElement instanceof AbstractElement) {
                    $texts[] = $this->extractElementText($childElement);
                }
            }
        }

        if (method_exists($element, 'getRows')) {
            foreach ($element->getRows() as $row) {
                if (! method_exists($row, 'getCells')) {
                    continue;
                }

                foreach ($row->getCells() as $cell) {
                    if (! method_exists($cell, 'getElements')) {
                        continue;
                    }

                    foreach ($cell->getElements() as $cellElement) {
                        if ($cellElement instanceof AbstractElement) {
                            $texts[] = $this->extractElementText($cellElement);
                        }
                    }
                }
            }
        }

        return trim(implode("\n", array_filter($texts)));
    }

    /**
     * Membersihkan teks hasil ekstraksi agar rapi untuk dibaca AI.
     */
    private function cleanText(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        /*
         * Rapikan spasi dan baris kosong berlebihan.
         */
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $this->maxLength);
    }
}