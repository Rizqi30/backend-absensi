<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departement_name' => 'required|string|max:255|unique:departments,departement_name',
            'max_clock_in_time' => 'required|date_format:H:i',
            'max_clock_out_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {

            DB::beginTransaction();

            $department = Department::create([
                'departement_name' => $request->departement_name,
                'max_clock_in_time' => $request->max_clock_in_time,
                'max_clock_out_time' => $request->max_clock_out_time,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Department berhasil ditambahkan',
                'data' => $department
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'message' => 'Department tidak di temukan'
            ], 404);
        }

        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department tidak ditemukan'], 404);
        }

        $validator = Validator::make(request()->all(), [
            'departement_name' => 'required|string|max:255|unique:departments,departement_name,' . $department->id,
            'max_clock_in_time' => 'required|date_format:H:i',
            'max_clock_out_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $department->update([
                'departement_name' => $request->departement_name,
                'max_clock_in_time' => $request->max_clock_in_time,
                'max_clock_out_time' => $request->max_clock_out_time,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Department berhasil diupdate',
                'data' => $department
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengupdate department',
                'error' => $e->getMessage()
            ], 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department tidak ditemukan'], 404);
        }

        try {
            DB::beginTransaction();

            $department->delete();

            DB::commit();

            return response()->json(['message' => 'Department berhasil dihapus'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus department',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
