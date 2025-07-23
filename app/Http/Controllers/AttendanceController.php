<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,employee_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $employee_id = $request->employee_id;
        $today = now()->toDateString();

        //Cek jika sudah melakukan check-in hari ini
        $existing = Attendance::where('employee_id', $employee_id)
            ->whereDate('clock_in', $today)
            ->first();
        
        if ($existing) {
            return response()->json(['message' => 'Anda sudah melakukan check-in hari ini'], 400);
        }

        try {
            DB::beginTransaction();

            $attendanceId = Str::uuid()->toString();
            $now = now();

            $attendance = Attendance::create([
                'employee_id' => $employee_id,
                'attendance_id' => $attendanceId,
                'clock_in' => $now,
            ]);

            AttendanceHistory::create([
                'employee_id' => $employee_id,
                'attendance_id' => $attendanceId,
                'date_attendance' => $now,
                'attendance_type' => 1, // Check-in
                'description' => 'Check-in',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Check-in berhasil',
                'data' => $attendance,
                'clock_in' => $now->format('H:i:s'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal melakukan check-in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkOut(Request $request, string $attendance_id)
    {
        $attendance = Attendance::where('attendance_id', $attendance_id)->first();

        if (!$attendance) {
            return response()->json(['message' => 'Data absensi tidak ditemukan'], 404);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Sudah melakukan check-out'], 400);
        }

        try {
            DB::beginTransaction();

            $now = now();

            $attendance->update([
                'clock_out' => $now,
            ]);

            AttendanceHistory::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance_id,
                'date_attendance' => $now,
                'attendance_type' => 2, // Check-out
                'description' => 'Check-out',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil check-out',
                'clock_out' => $now->format('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal melakukan check-out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['employee.department']);
        
        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $query->whereDate('clock_in', $request->date);
        }

        // Filter berdasarkan departemen
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('departement_id', $request->department_id);
            });
        }

        $records = $query->get()->map(function ($row) {
            $dept = $row->employee->department;
            // $clockIn = optional($row->clock_in)?->format('H:i:s');
            // $clockOut = optional($row->clock_out)?->format('H:i:s');
            $clockIn = $row->clock_in ? \Carbon\Carbon::parse($row->clock_in)->format('H:i:s') : null;
            $clockOut = $row->clock_out ? \Carbon\Carbon::parse($row->clock_out)->format('H:i:s') : null;

            // $statusIn = $clockIn && $clockIn <= $dept->max_clock_in_time ? 'Ontime' : 'Late';
            // $statusOut = $clockOut && $clockOut >= $dept->max_clock_out_time ? 'Ontime' : 'Early Leave';
            $statusIn = $clockIn ? ($clockIn <= $dept->max_clock_in_time ? 'Ontime' : 'Late') : '-';
            $statusOut = $clockOut ? ($clockOut >= $dept->max_clock_out_time ? 'Ontime' : 'Early Leave') : '-';


            return [
                'employee_id' => $row->employee_id,
                'attendance_id' => $row->attendance_id,
                'name' => $row->employee->name,
                'department' => $dept->departement_name,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'check_in_status' => $clockIn ? $statusIn : '-',
                'check_out_status' => $clockOut ? $statusOut : '-',
            ];
        });

        return response()->json($records);
        dd($records);
    }
}

