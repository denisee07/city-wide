<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Human-friendly label + hint for every key stored in the settings table. */
    public const FIELDS = [
        'barangay_name' => [
            'label' => 'City / office name',
            'hint' => 'Shown in the brand mark, page titles and printed documents.',
            'placeholder' => 'Santa Rosa City',
        ],
        'barangay_address' => [
            'label' => 'Office address',
            'hint' => 'Complete mailing address used on certificates.',
            'placeholder' => 'City Hall Complex, Rizal Boulevard, Santa Rosa City, Laguna 4026',
        ],
        'barangay_contact' => [
            'label' => 'Contact number / email',
            'hint' => 'Hotline or e-mail printed on public pages.',
            'placeholder' => '(049) 534-3000 / eservices@santrosacity.gov.ph',
        ],
        'barangay_capain' => [
            'label' => 'Punong Barangay',
            'hint' => 'Printed as the signatory on certificates and other documents this office issues.',
            'placeholder' => 'Hon. Rodrigo M. Bautista',
        ],
        'barangay_motto' => [
            'label' => 'Motto',
            'hint' => 'Short tagline for the landing page hero.',
            'placeholder' => 'Makabuluhan, mapagkalinga, magkaisa.',
        ],
    ];

    /**
     * These keys drive the brand mark, page titles and the wording printed on
     * certificates. With 18 barangays sharing one portal there is no single
     * "barangay name" to edit, so they describe the city office and every
     * officer maintains them from the same screen.
     */
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::kv(),
            'fields' => self::FIELDS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barangay_name' => ['required', 'string', 'max:120'],
            'barangay_address' => ['nullable', 'string', 'max:220'],
            'barangay_contact' => ['nullable', 'string', 'max:120'],
            'barangay_capain' => ['nullable', 'string', 'max:140'],
            'barangay_motto' => ['nullable', 'string', 'max:220'],
        ]);

        foreach (array_keys(self::FIELDS) as $key) {
            Setting::set($key, trim($data[$key] ?? ''));
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Barangay settings saved and published across the site.');
    }
}
