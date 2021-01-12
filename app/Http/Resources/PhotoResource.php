<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        /////    send response with specified information
        if ( $this->resource[ 'status' ] == Response::HTTP_OK && strlen( $this->resource['content'] ) > 10 )
        {
            $result = json_decode( $this->resource['content'] );
            // echo '<pre>';
            // print_r($this->resource);
            $new_photos_collection = collect( $result->photos->photo )->map( function ( $item ) {
                // print_r($item);
                return [
                    'id'            => $item->id,
                    'title'         => $item->title ?? '',
                    'ownername'     => $item->ownername ?? '',

                    /////   not all photos return original url + (related) dimensions -
                    /////   is because this is a free account (?)
                    'url'           => isset( $item->url_o )  ? $item->url_o : ( isset( $item->url_l ) ? $item->url_l : ( $item->url_m )) ,
                    'width'         => isset( $item->width_o )  ? $item->width_o : ( isset( $item->width_l ) ? $item->width_l : ( $item->width_m )) ,
                    'height'        => isset( $item->height_o )  ? $item->height_o : ( isset( $item->height_l ) ? $item->height_l : ( $item->height_m )) ,

                    'description'   => $item->description->_content ?? '',
                ] ;
            }) ;

            /////   is a colection to return
            if ( ! $this->resource[ 'is_single'] )
            {
                return [
                    'num_pages'     => $result->photos->pages,
                    'actual_page'   => $result->photos->page,
                    'total_photos'  => $result->photos->total,
                    'photos_collection' => $new_photos_collection,
                ];
            }
            /////   only a single photo
            return $new_photos_collection ;
        }
        return  $this->resource;
    }
}
