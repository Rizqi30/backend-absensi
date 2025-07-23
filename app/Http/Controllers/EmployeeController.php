<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employee = Employee::with('department')->get();

        return response()->json($employee);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|unique:employees,employee_id|max:50',
            'departement_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $employee = Employee::create([
                'employee_id' => $request->employee_id,
                'departement_id' => $request->departement_id,
                'name' => $request->name,
                'address' => $request->address,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $employee
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Employee::with('department', 'attendances', 'attendancesHistory')->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Karyawan Tidak Ditemukan'], 404);
        }

        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Karyawan Tidak Ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|max:50|unique:employees,employee_id,' . $id,
            'departement_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $employee->update([
                'employee_id' => $request->employee_id,
                'departement_id' => $request->departement_id,
                'name' => $request->name,
                'address' => $request->address,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Karyawan berhasil diperbarui',
                'data' => $employee
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Karyawan Tidak Ditemukan'], 404);
        }

        try {
            DB::beginTransaction();

            $employee->delete();

            DB::commit();

            return response()->json(['message' => 'Karyawan berhasil dihapus']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
