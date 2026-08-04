<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Models\Porduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['porducts'] = Porduct::orderBy('id','asc')->paginate(50);
        return  view('recipes.index ', $data);
    }

    
    /** 
    * Show the form for editing the specified resource.
    *
    * @param  \App\Recipe  $recipe
    * @return \Illuminate\Http\Response
    */
    public function create(Request $request)
    {
        $productId = $request->get('productId');
        Log::info(" getting id from model ------------------------------------------- : ".$productId); 
        $recipesList=DB::table('recipes')->where('productId', $productId)->get();
        Log::info("list ------------------------------------------- : ".$recipesList); 
        $porduct = Porduct::find($productId);
        Log::info(" - 1 Update Job Card ------------------------------------------- : ".$porduct); 
    
        View::share('porduct',$porduct);
        View::share('recipes',$recipesList);
     
        return view('recipes.create', ['productId' => $productId]);
     
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $recipe = new Recipe;
        $recipe->name = $request->name;
        $recipe->productId = $request->productId;
        $recipe->reference = $request->reference;
        $recipe->productAllocationId = $request->productAllocationId;
        $recipe->allocationTypeId = $request->allocationTypeId;
        $recipe->quantityAllocation = $request->quantityAllocation;
        $recipe->qntUnitId = $request->qntUnitId;
        $recipe->save();

        $recipe->save();
        return redirect()->route('recipes.create',['productId' => $recipe->productId])->with('success','Recipe has been created successfully.');
        
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function show(Recipe $recipe)
    {
        return view('recipes.show',compact('recipe'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function edit(Recipe $recipe)
    {
        $recipeList=DB::table('recipes')->where('productId', $recipe->productId)->get();
  
        $porduct = Porduct::find($recipe->productId);

        View::share('porduct',$porduct);
        Log::info(" getting id from model ------------------------------------------- : ".$porduct);
        View::share('recipes', $recipeList);
        Log::info(" -WwoooowW------------------------------------------- : ".$recipe->id); 
     
        return view('recipes.edit',compact('recipe'));
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
 
        ]);
        $recipe = Recipe::find($id);
        $recipe->name = $request->name;
        $recipe->reference = $request->reference;
        $recipe->productAllocationId = $request->productAllocationId;
        $recipe->allocationTypeId = $request->allocationTypeId;
        $recipe->quantityAllocation = $request->quantityAllocation;
        $recipe->qntUnitId = $request->qntUnitId;
        $recipe->save();
        return redirect()->route('recipes.create',['productId' => $recipe->productId])->with('success','Recipe has been created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('recipes.create',['productId' => $recipe->productId])
        ->with('success','Recipe has been deleted successfully');
    }
}
