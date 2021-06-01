<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Testimonial;
use Illuminate\Http\Request;
use App\Http\Resources\Backoffice\DataTable\TestimonialResource;
use App\Http\Resources\Backoffice\DataTable\TestimonialCollection;
use App\Http\Requests\Backoffice\Testimonial\StoreRequest;
use App\Http\Requests\Backoffice\Testimonial\UpdateRequest;
use Storage;
use Str;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $testimonials = Testimonial::when($request->search, function ($query, $search) {

            $query->where(

                'id', 'like', "$search%"

            )->orWhere(

                'name', 'like', "%$search%"

            )->orWhere(

                'testimonial', 'like', "%$search%"
            );

        })->orderBy(

            $request->order_column,
            $request->order_direction,

        )->paginate($request->per_page, ['*'], 'page', $request->page);

        return new TestimonialCollection($testimonials);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Backoffice\Testimonial\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        # upload cover
        
        $cover = $request->hasFile('cover') ? $request->file('cover')->store('posts/cover') : null;

        # save

        $testimonial = Testimonial::create( array_merge(

            $request->all(), compact('cover')
        ));
        
        return new TestimonialResource($testimonial);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Testimonial  $testimonial
     * @return \Illuminate\Http\Response
     */
    public function edit(Testimonial $testimonial)
    {
        return new TestimonialResource($testimonial);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Backoffice\Testimonial\UpdateRequest  $request
     * @param  \App\Testimonial  $testimonial
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Testimonial $testimonial)
    {
        # upload cover
        
        if ( $request->hasFile('cover') ) {

            $cover = $request->file('cover')->store('posts/cover');

            Storage::delete($testimonial->cover);
        }
        else
            $cover = $testimonial->cover;

        # save changes

        $testimonial->update( array_merge($request->only([

            'name', 'testimonial',

        ]), compact('cover')) );

        return new TestimonialResource($testimonial);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Testimonial  $testimonial
     * @return \Illuminate\Http\Response
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
    }
}
