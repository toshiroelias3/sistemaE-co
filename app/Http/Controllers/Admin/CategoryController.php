<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Admin\CategoryRequest; // Importación correcta
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Mostrar lista de categorías
    public function index()
    {
        $categories = Category::with('parent')->withCount('products')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    // Formulario de creación
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    // Guardar nueva categoría
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());
        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    // Formulario de edición
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    // Actualizar categoría
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    // Eliminar categoría
    public function destroy(Category $category)
    {
        // Las subcategorías se borrarían en cascada, así que se exige eliminarlas primero
        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene subcategorías. Elimínalas o reasígnalas primero.');
        }

        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque contiene productos activos.');
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada correctamente.');
    }
}