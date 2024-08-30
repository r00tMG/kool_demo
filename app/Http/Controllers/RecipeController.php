<?php

namespace App\Http\Controllers;

use App\Category;
use App\Ingredient;
use App\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class RecipeController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::with('ingredientss')->with('categories')->orderBy('created_at', 'DESC')->get();
        return view('recipes.recipes',[
            'recipes' => $recipes
        ]);

    }

    public function create()
    {
        $ingredients = Ingredient::all();

        return view('recipes.create-recipe',[
            'ingredients' => $ingredients,
            'categories' => Category::all()

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        //dd('salut');
        $rules = [
            'title' => 'required|string|max:255',
            'image' => 'required|file',
            'summary' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode('\\n', $errors);

            return response()->make("<script>
                alert('Bad request: $errorMessage');
                window.history.back();
            </script>");
        }
        $validatedData = $validator->validated();
        //dd($validatedData);
        if ($request->hasFile('image')){
            $validatedData['image'] = $request->file('image')->store('images');
        }
        //$validatedData['image'] = $validatedData->image->store('uploads');
        try {
            DB::beginTransaction();

            $recipe = Recipe::create([
                'title' => $validatedData['title'],
                'image' => $validatedData['image'],
                'summary' => $validatedData['summary'],
            ]);
            //dd($validatedData['ingredients']);
            $ingredients = [];
            foreach ($validatedData['ingredients'] as $ingredientData) {
                $ingredient = Ingredient::firstOrCreate(
                    ['name' => $ingredientData['name']],
                    ['quantity' => $ingredientData['quantity']]
                );
                $ingredients[$ingredient->id] = ['quantity' => $ingredientData['quantity']];
            }
            //dd($ingredients);

            $recipe->ingredientss()->attach($ingredients);

            $recipe->categories()->attach($validatedData['categories']);
            //dd($recipe);
            DB::commit();

            $recipe->load('ingredientss');
            //dd($recipe);

            return to_route('recipes.index');

        } catch (\Exception $e) {
            DB::rollBack();
                $errorMessage =  $e->getMessage();
                return response()->make("<script>
                alert('Bad request: $errorMessage');
                window.history.back();
            </script>");


        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $recipe = Recipe::with('ingredientss')->with('categories')->find($id);
        return \response()->json([
            'recipe' => $recipe
        ],Response::HTTP_OK);
    }

    public function edit(string $id)
    {
        $recipe = Recipe::with('ingredientss')->with('categories')->find($id);
        $categories = Category::all();
        //dd($recipe->ingredientss);
        return view('recipes.edit-recipe',[
            'recipe' => $recipe,
            'categories' => $categories
        ]);
    }
    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'image' => 'required|file',
            'summary' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode('\\n', $errors);

            return response()->make("<script>
                alert('Bad request: $errorMessage');
                window.history.back();
            </script>");
        }

        $validatedData = $validator->validated();
        $recipe = Recipe::with('ingredientss')->with('categories')->find($id);
        //dd($recipe);
        logger('recipe with ingredients',['recipe'=>$recipe]);
        try {
            DB::beginTransaction();

            $recipe->update([
                'title' => $validatedData['title'],
                'image' => $validatedData['image'],
                'summary' => $validatedData['summary'],
            ]);
            logger('recipe ', ['recipe'=>$recipe]);
            $recipe->ingredientss()->detach();
            logger('recipe detached', ['recipe'=>$recipe]);

            $ingredients = [];

            foreach ($validatedData['ingredients'] as $ingredientData) {
                //dd($ingredientData['name']);
                $ingredient = Ingredient::firstOrCreate(
                    ['name' => $ingredientData['name']],
                    ['quantity' => $ingredientData['quantity']]
                );

                $ingredients[$ingredient->id] = ['quantity' => $ingredientData['quantity']];
            }
            $recipe->ingredientss()->attach($ingredients);

            $recipe->categories()->attach($validatedData['categories']);
            DB::commit();

            $recipe->load('ingredientss');
            //dd($recipe);
            logger('final recipe', ['recipe' => $recipe]);
            return to_route('recipes.index')->with('success', 'Your recipe have been updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

                $errorMessage =  $e->getMessage();
                return response()->make("<script>
                alert('Bad request: $errorMessage');
                window.history.back();
            </script>");


        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //dd($id);
        $recipe = Recipe::with('ingredientss')->with('categories')->find($id);
        //dd($recipe);
        if ($recipe === null) {
            $errorMessage = 'Recipe not found';
            return response()->make("<script>
                alert('Bad request: $errorMessage');
                window.history.back();
            </script>");
        }
        $recipe->delete();
        return to_route('recipes.index')->with('success','Your recipe have been deleted with successfully');

    }
}
