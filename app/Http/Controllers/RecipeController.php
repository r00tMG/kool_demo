<?php

namespace App\Http\Controllers;

use App\Category;
use App\Ingredient;
use App\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class RecipeController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::with('ingredientss')->orderBy('created_at', 'DESC')->get();
        #dd($recipes);
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
            return response()->json([
                'id' => $recipe->id,
                'title' => $recipe->title,
                'image' => $recipe->image,
                'summary' => $recipe->summary,
                'ingredients' => $recipe->ingredients->map(function ($ingredient) {
                    return [
                        'name' => $ingredient->name,
                        'quantity' => $ingredient->pivot->quantity,
                    ];
                }),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while creating the recipe.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //dd($id);
        $recipe = Recipe::with('ingredientss')->find($id);
        return \response()->json([
            'recipe' => $recipe
        ],Response::HTTP_OK);
    }

    public function edit(string $id)
    {
        $recipe = Recipe::with('ingredientss')->find($id);
        //dd($recipe->ingredientss);
        return view('recipes.edit-recipe',[
            'recipe' => $recipe
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $rules = [
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'summary' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'required|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        /*if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }*/

        $validatedData = $validator->validated();
        $recipe = Recipe::with('ingredientss')->find($id);
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
            dd($validatedData['ingredients']);

            foreach ($validatedData['ingredients'] as $ingredientData) {
                //dd($ingredientData['name']);
                $ingredient = Ingredient::firstOrCreate(
                    ['name' => $ingredientData['name']],
                    ['quantity' => $ingredientData['quantity']]
                );

                $ingredients[$ingredient->id] = ['quantity' => $ingredientData['quantity']];
                dd($ingredients);
            }
            dd($ingredients);
            $recipe->ingredientss()->attach($ingredients);

            DB::commit();

            $recipe->load('ingredientss');
            //dd($recipe);
            logger('final recipe', ['recipe' => $recipe]);
            return to_route('recipes.index')->with('success', 'Your recipe have been updated successfully');
            return response()->json([

                'recipe' => $recipe
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the recipe.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //dd($id);
        $recipe = Recipe::with('ingredientss')->find($id);
        //dd($recipe);
        /*if ($recipe === null)
        {
            return \response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Recette introuvable'
            ]);
        }*/
        $recipe->delete();
        return to_route('recipes.index')->with('success','Your recipe have been deleted with successfully');
        return \response()->json([
            'status' => Response::HTTP_NO_CONTENT,
            'message' => 'Recette supprimée avec succeés'
        ]);
    }
}
