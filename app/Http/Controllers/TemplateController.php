<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Templates/Index', [
            'templates' => Template::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Templates/Builder');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:templates,name',
            'type' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string|in:text,textarea,dropdown,checkbox',
            'fields.*.options' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $template = Template::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
            ]);

            foreach ($validated['fields'] as $index => $fieldData) {
                $template->fields()->create([
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'],
                    'options' => $fieldData['type'] === 'dropdown' ? explode(',', $fieldData['options']) : null,
                    'order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('templates.index')->with('success', 'Template created successfully.');
    }

    public function edit(Template $template): Response
    {
        $template->load('fields');

        return Inertia::render('Admin/Templates/Builder', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:templates,name,' . $template->id,
            'type' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string|in:text,textarea,dropdown,checkbox',
            'fields.*.options' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $template) {
            $template->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
            ]);

            // Delete old fields and create new ones to ensure order and content is correct
            $template->fields()->delete();

            foreach ($validated['fields'] as $index => $fieldData) {
                $template->fields()->create([
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'],
                    'options' => $fieldData['type'] === 'dropdown' ? explode(',', $fieldData['options']) : null,
                    'order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }
}
