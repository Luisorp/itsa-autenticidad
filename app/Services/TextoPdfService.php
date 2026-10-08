<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class TextoPdfService
{
    public function __construct(private readonly Parser $parser) {}

    public function extraer(string $ruta): ?string
    {
        try {
            $texto = $this->parser->parseFile($ruta)->getText();
        } catch (\Exception) {
            // Se conserva el PDF aunque esté escaneado o no se pueda extraer texto.
            return null;
        }

        // Algunos PDF devuelven pares sustitutos UTF-16 codificados como CESU-8.
        // Recuperamos sus símbolos antes de sustituir otros bytes inválidos.
        $texto = preg_replace_callback(
            '/\xED[\xA0-\xAF][\x80-\xBF]\xED[\xB0-\xBF][\x80-\xBF]/',
            function (array $coincidencia): string {
                $bytes = $coincidencia[0];
                $alto = ((ord($bytes[0]) & 0x0F) << 12) | ((ord($bytes[1]) & 0x3F) << 6) | (ord($bytes[2]) & 0x3F);
                $bajo = ((ord($bytes[3]) & 0x0F) << 12) | ((ord($bytes[4]) & 0x3F) << 6) | (ord($bytes[5]) & 0x3F);

                return mb_chr(0x10000 + (($alto - 0xD800) << 10) + ($bajo - 0xDC00), 'UTF-8');
            },
            $texto
        );

        return mb_scrub($texto, 'UTF-8');
    }
}
