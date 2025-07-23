<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('employees', EmployeeController::class)->except(['create', 'edit']);
Route::apiResource('departments', DepartmentController::class)->except(['create', 'edit']);

Route::post('/attendances/checkin', [AttendanceController::class, 'checkIn']);
Route::put('/attendances/checkout/{attendance_id}', [AttendanceController::class, 'checkOut']);
Route::get('/attendances', [AttendanceController::class, 'index']);