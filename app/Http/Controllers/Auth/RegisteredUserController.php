<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create device via relationship
            $device = $user->device()->create([
                'status' => 'offline',
                'last_seen' => null,
            ]);

            // Create device settings
            $device->deviceSettings()->create([
                'temp1_threshold' => 0.00,
                'temp2_threshold' => 0.00,
            ]);

            // Create initial sensor data
            $device->sensorData()->create([
                'battery_a' => 0.00,
                'battery_b' => 0.00,
                'battery_c' => 0.00,
                'battery_d' => 0.00,
                'temperature_1' => 0.00,
                'temperature_2' => 0.00,
                'pln_volt' => 0.00,
                'pln_current' => 0.00,
                'pln_power' => 0.00,
                'relay_1' => false,
                'relay_2' => false,
            ]);
            $device->sensorDataStill()->create([
                'battery_a' => 0.00,
                'battery_b' => 0.00,
                'battery_c' => 0.00,
                'battery_d' => 0.00,
                'temperature_1' => 0.00,
                'temperature_2' => 0.00,
                'pln_volt' => 0.00,
                'pln_current' => 0.00,
                'pln_power' => 0.00,
                'relay_1' => false,
                'relay_2' => false,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard.index', absolute: false));
    }
}
