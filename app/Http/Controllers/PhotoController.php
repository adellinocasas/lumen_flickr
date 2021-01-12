<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client ;

class PhotoController extends Controller
{
    /**
     * Default parameters toi use in fetch
     */
    private $request_default_params = [
        'per_page' => 20,
        'page' => 1,
        'search_by' => null,
    ];

    /**
     * contrains minimum length for a text serarch
     */
    private $minimum_length_text_search = 2;

    /**
     * the API Key from .env
     */
    private $flickr_api_key ;

    /**
     * is a single photo or a collection
     */
    private $is_single_request= false;


    /**
     * initializes the fetch
     */
    public function __construct()
    {
        $this->flickr_api_key =  env( "FLICK_KEY" );
    }


    /**
     * Fetch a bunch of photos from Flickr
     * @return JsonResource
     */
    public function index(): mixed
    {

        $this->request_default_params[ 'per_page' ] = ! request()->input( 'per_page' ) ? $this->request_default_params[ 'per_page' ] : request()->input( 'per_page' ) ;
        $this->request_default_params[ 'page' ] =  ! request()->input( 'page' ) ? $this->request_default_params[ 'page' ] : request()->input( 'page' ) ;
        $this->request_default_params[ 'search_by' ] = request()->input( 'search_by' );
        $this->is_single_request = false;

        $result = $this->prepareFlickrRequest();

        return new PhotoResource( $result ) ;
    }


    /**
     * Fetch a random photo from Flickr
     * @return JsonResource
     */
    public function get_random_photo(): mixed
    {
        $page = rand( 0, 99999 );

        $this->request_default_params[ 'page' ] = $page;
        $this->request_default_params[ 'per_page' ] = 1;
        $this->is_single_request = true;

        $result = $this->prepareFlickrRequest();

        return new PhotoResource( $result ) ;
    }


    /**
     * Preparation of a fetch request of photo(s) from Flickr
     * @return JsonResource
     */
    private function prepareFlickrRequest(): array
    {
        /////  clean terms of search: alphanumeric !!, and leave spaces between words
        $search_by = trim( preg_replace('/[^\\w. ]/i', '', $this->request_default_params[ 'search_by' ]));
        /////   remove spaces: count only alfanumeric chars
        $searc_by_only_with_alfanumneric_chars = str_replace( ' ', '', $search_by );

        /////  search term at least with X chars
        if ( $searc_by_only_with_alfanumneric_chars && strlen( $search_by ) < $this->minimum_length_text_search )
        {
            return [
                'content' => [],
                'message' => 'search term invalid',
                'status' => Response::HTTP_NOT_ACCEPTABLE
            ];
        }

        /////   build the Flickr end point
        /////   endpoint depends on the existence of a text to searching for
        $url = 'https://www.flickr.com/services/rest/?method=flickr.photos.';

        $url .= $search_by ? 'search&tags='. urlencode( $search_by ) : 'getRecent' ;

        $url .= '&api_key='. $this->flickr_api_key .'&privacy_filter=public&format=json&nojsoncallback=1&per_page='. $this->request_default_params[ 'per_page' ] .'&page='. $this->request_default_params[ 'page' ] . '&extras=url_o,url_l,url_m,owner_name,description,original_format' ;

        return [
            'content'   => $this->makeRequest( $url ),
            'message'   => 'OK',
            'status'    => Response::HTTP_OK,
            'is_single' => $this->is_single_request,
        ];

    }

    /**
     * Make the cURL request to Flickr
     * @return string of Json data
     */
    private function makeRequest( $url )
    {
        $client = new Client;

        try {
            $response = $client->get( $url, []);
            return $response->getBody();

        } catch( Exception $e ) {

        }
    }


    /**
     * Make the cURL request to Flickr
     * @return string of Json data
     */
    /*
    // ##### OLD IMPLEMENTATION WITH cURL
    private function makeRequest_old( $url ): string
    {
        try {

            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_URL, $url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

            $result = curl_exec( $curl );
            curl_close( $curl );
            return $result;

        } catch( Exception $e ) {

        }
    }
    */

}
