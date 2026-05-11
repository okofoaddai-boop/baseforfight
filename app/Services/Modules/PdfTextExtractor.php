<?php

namespace App\Services\Modules;

use RuntimeException;
use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    public function extract(string $absolutePath): string
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('PDF-Datei wurde nicht gefunden.');
        }

        $parser = new Parser();
        $document = $parser->parseFile($absolutePath);
        $text = (string) $document->getText();

        // Normalize horizontal whitespace but keep line structure useful for extraction.
        $text = preg_replace('/[\t ]+/', ' ', $text);
        $text = preg_replace('/\R{3,}/', "\n\n", (string) $text);
        $text = trim((string) $text);

        if ($text === '') {
            throw new RuntimeException('PDF enthält keinen auslesbaren Text.');
        }

        return $text;
    }
}
