<?php

namespace App\Http\Controllers;

use App\Services\CrossrefService;
use App\Services\OpenAlexService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

class CrossrefController extends Controller
{
    public function index(Request $request, CrossrefService $crossref, OpenAlexService $openAlex)
    {
        $resultados = null;
        $datos = null;

        if ($request->filled('consulta')) {
            $request->merge(['fuente' => $request->input('fuente', 'crossref')]);
            $datos = $request->validate([
                'fuente' => ['required', 'in:crossref,openalex'],
                'tipo' => ['required', 'in:titulo,doi'],
                'consulta' => ['required', 'string', 'max:500'],
            ]);

            try {
                $servicio = $datos['fuente'] === 'openalex' ? $openAlex : $crossref;
                $resultados = $servicio->buscar($datos['tipo'], $datos['consulta']);
            } catch (ConnectionException) {
                return back()->withInput()->with('error', 'No se pudo conectar con '.$this->nombreFuente($datos['fuente']).'. Inténtalo nuevamente en unos momentos.');
            } catch (RequestException $e) {
                $fuente = $this->nombreFuente($datos['fuente']);
                $mensaje = $e->response?->status() === 404
                    ? $fuente.' no encontró un registro para ese DOI.'
                    : $fuente.' no pudo procesar la consulta en este momento.';

                return back()->withInput()->with('error', $mensaje);
            }
        }

        return view('crossref.index', compact('resultados', 'datos'));
    }

    private function nombreFuente(string $fuente): string
    {
        return $fuente === 'openalex' ? 'OpenAlex' : 'Crossref';
    }
}
