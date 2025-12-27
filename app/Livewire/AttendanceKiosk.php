<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class AttendanceKiosk extends Component
{
    public $employees;
    public $selectedEmployee = null;
    public $pin = '';
    public $message = '';
    public $status = ''; // 'success' or 'error'

    public function mount()
    {
        // Ambil karyawan aktif saja
        $this->employees = Employee::where('is_active', true)->get();
    }

    public function selectEmployee($id)
    {
        $this->selectedEmployee = Employee::find($id);
        $this->pin = ''; // Reset PIN
        $this->message = '';
    }

    public function submit()
    {
        // 1. Validasi PIN
        if ($this->selectedEmployee->pin !== $this->pin) {
            $this->message = 'PIN Salah!';
            $this->status = 'error';
            $this->pin = '';
            return;
        }

        // 2. Cek apakah sudah absen hari ini?
        $today = Carbon::today();
        $attendance = Attendance::where('employee_id', $this->selectedEmployee->id)
            ->where('date', $today)
            ->first();

        // 3. Logic Clock In / Clock Out
        if (!$attendance) {
            // Belum absen -> CLOCK IN
            Attendance::create([
                'employee_id' => $this->selectedEmployee->id,
                'date' => $today,
                'clock_in' => Carbon::now(),
                'status' => 'present',
            ]);
            $this->message = "Halo {$this->selectedEmployee->name}, Selamat Bekerja!";
            $this->status = 'success';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            // Sudah masuk, belum pulang -> CLOCK OUT
            $attendance->update([
                'clock_out' => Carbon::now(),
            ]);
            $this->message = "Terima kasih {$this->selectedEmployee->name}, Hati-hati di jalan!";
            $this->status = 'success';
        } else {
            // Sudah pulang -> Error
            $this->message = "Anda sudah selesai bekerja hari ini.";
            $this->status = 'error';
        }

        // Reset UI setelah 3 detik
        $this->dispatch('attendance-logged'); // Trigger JS reset (opsional)
        $this->reset(['selectedEmployee', 'pin']);
    }

    // Agar menggunakan layout minimalis (bukan layout admin)
    #[Layout('components.layouts.app')] 
    public function render()
    {
        return view('livewire.attendance-kiosk');
    }
}