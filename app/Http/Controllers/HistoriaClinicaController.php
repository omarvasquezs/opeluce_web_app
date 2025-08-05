<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HistoriaClinicaController extends Controller
{
    /**
     * Display a listing of historia clinica records
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = HistoriaClinica::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('paciente', 'LIKE', "%{$search}%")
                      ->orWhere('dni', 'LIKE', "%{$search}%")
                      ->orWhere('hc_num', 'LIKE', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $historias = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json($historias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving historia clinica records',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created historia clinica record
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'hc_num' => 'nullable|string|max:20',
                'paciente' => 'required|string|max:100',
                'dni' => 'required|string|max:20',
                'fecha_nacimiento' => 'nullable|date',
                'edad' => 'nullable|integer',
                'fecha_atencion' => 'nullable|date',
                'raza' => 'nullable|string|max:50',
                'sexo' => 'nullable|in:F,M',
                'hora_atencion' => 'nullable|string|max:10',
                'ocupacion' => 'nullable|string|max:100',
                'afiliacion' => 'nullable|string|max:100',
                'domicilio' => 'nullable|string|max:200',
                'acompanante' => 'nullable|string|max:100',
                'dni_acompanante' => 'nullable|string|max:20'
            ]);

            $historia = HistoriaClinica::create($validatedData);

            return response()->json([
                'message' => 'Historia clinica created successfully',
                'data' => $historia
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating historia clinica record',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified historia clinica record
     */
    public function show($id): JsonResponse
    {
        try {
            $historia = HistoriaClinica::findOrFail($id);
            return response()->json($historia);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Historia clinica record not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified historia clinica record
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $historia = HistoriaClinica::findOrFail($id);

            $validatedData = $request->validate([
                'hc_num' => 'nullable|string|max:20',
                'paciente' => 'required|string|max:100',
                'dni' => 'required|string|max:20',
                'fecha_nacimiento' => 'nullable|date',
                'edad' => 'nullable|integer',
                'fecha_atencion' => 'nullable|date',
                'raza' => 'nullable|string|max:50',
                'sexo' => 'nullable|in:F,M',
                'hora_atencion' => 'nullable|string|max:10',
                'ocupacion' => 'nullable|string|max:100',
                'afiliacion' => 'nullable|string|max:100',
                'domicilio' => 'nullable|string|max:200',
                'acompanante' => 'nullable|string|max:100',
                'dni_acompanante' => 'nullable|string|max:20'
            ]);

            $historia->update($validatedData);

            return response()->json([
                'message' => 'Historia clinica updated successfully',
                'data' => $historia
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating historia clinica record',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified historia clinica record
     */
    public function destroy($id): JsonResponse
    {
        try {
            $historia = HistoriaClinica::findOrFail($id);
            $historia->delete();

            return response()->json([
                'message' => 'Historia clinica deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error deleting historia clinica record',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for patients (specific endpoint for the patient search modal)
     */
    public function searchPatients(Request $request): JsonResponse
    {
        try {
            $query = HistoriaClinica::query();

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('paciente', 'LIKE', "%{$search}%")
                      ->orWhere('dni', 'LIKE', "%{$search}%")
                      ->orWhere('hc_num', 'LIKE', "%{$search}%");
                });
            }

            // Pagination with 20 records per page
            $perPage = $request->get('per_page', 20);
            $patients = $query->orderBy('paciente', 'asc')->paginate($perPage);

            return response()->json($patients);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error searching patients',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
