<?php

/**
 * Class TwigExtension
 *
 * Provide a set of methods which can be used in template engine
 *
 */

namespace Metabolism\WordpressBundle\Twig;

use Metabolism\WordpressBundle\Entity\Blog;
use Metabolism\WordpressBundle\Entity\Image;
use Metabolism\WordpressBundle\Factory\Factory;
use Metabolism\WordpressBundle\Factory\PostFactory;
use Metabolism\WordpressBundle\Factory\TermFactory;

use Twig\Extension\AbstractExtension,
    Twig\TwigFilter,
    Twig\TwigFunction;
use Twig\Markup;

class WordpressTwigExtension extends AbstractExtension{

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter( 'handle', 'sanitize_title' ),
            new TwigFilter( 'placeholder', [$this, 'placeholder'] ),
            new TwigFilter( 'more', [$this, 'more'] ),
            new TwigFilter( 'resize', [$this, 'resize'] ),
            new TwigFilter( 'picture', [$this, 'picture'] ),
            new TwigFilter( 'blurhash', [$this, 'blurhash'] ),
            new TwigFilter( 'figure', [$this, 'figure'] ),
            new TwigFilter( 'stripshortcodes','strip_shortcodes' ),
            new TwigFilter( 'trim_words','wp_trim_words' ),
            new TwigFilter( 'function', [$this, 'execFunction'] ),
            new TwigFilter( 'excerpt','wp_trim_words' ),
            new TwigFilter( 'sanitize','sanitize_title' ),
            new TwigFilter( 'base64_encode','base64_encode' ),
            new TwigFilter( 'base64_decode','base64_decode' ),
            new TwigFilter( 'shortcodes', [$this, 'doShortcode'] ),
            new TwigFilter( 'wpautop','wpautop' ),
            new TwigFilter( 'array',[$this, 'toArray'] ),
            new TwigFilter( 'file_exists',[$this, 'fileExists'] ),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        $blog = Blog::getInstance();

        return [
            new TwigFunction( 'placeholder', [$this, 'generatePlaceholder'] ),
            new TwigFunction( 'pixel', [$this, 'generatePixel'] ),
            new TwigFunction( 'fn', [$this, 'execFunction'] ),
            new TwigFunction( 'function', [$this, 'execFunction'] ),
            new TwigFunction( 'action', [$this, 'doAction'] ),
            new TwigFunction( 'shortcode', 'do_shortcode' ),
            new TwigFunction( 'login_url', 'wp_login_url' ),
            new TwigFunction( 'home_url', 'get_home_url' ),
            new TwigFunction( 'search_form', 'get_search_form' ),
            new TwigFunction( 'archive_url', [$blog, 'getArchiveLink'] ),
            new TwigFunction( 'archive_title', [$blog, 'getArchiveTitle'] ),
            new TwigFunction( 'attachment_url', 'wp_get_attachment_url' ),
            new TwigFunction( 'post_url', [$this, 'getPermalink'] ),
            new TwigFunction( 'term_url', [$this, 'getTermLink'] ),
            new TwigFunction( 'bloginfo', 'get_bloginfo' ),
            new TwigFunction( 'dynamic_sidebar', function($id){ return $this->getOutput('dynamic_sidebar', [$id]); }, ['is_safe' => array('html')]  ),
            new TwigFunction( 'comment_form', function($post_id, $args=[]){ return $this->getOutput('comment_form', [$args, $post_id]); }, ['is_safe' => array('html')]  ),
            new TwigFunction( 'is_active_sidebar', 'is_active_sidebar' ),
            new TwigFunction( '_e', 'translate' ),
            new TwigFunction( '_x', '_x' ),
            new TwigFunction( '_ex', '_ex' ),
            new TwigFunction( '_nx', '_nx' ),
            new TwigFunction( '_n_noop', '_n_noop' ),
            new TwigFunction( '_nx_noop', '_nx_noop' ),
            new TwigFunction( '_n', '_n' ),
            new TwigFunction( '__', 'translate' ),
            new TwigFunction( 'translate', 'translate' ),
            new TwigFunction( 'translate_nooped_plural', 'translate_nooped_plural' ),
            new TwigFunction( 'wp_head', function(){ return @$this->getOutput('wp_head'); }, ['is_safe' => array('html')]  ),
            new TwigFunction( 'wp_footer', function(){ return @$this->getOutput('wp_footer'); }, ['is_safe' => array('html')]  ),
            new TwigFunction( 'Post', function($id){ return PostFactory::create($id); } ),
            new TwigFunction( 'User', function($id){ return Factory::create($id, 'user'); } ),
            new TwigFunction( 'Term', function($id){ return TermFactory::create($id); } ),
            new TwigFunction( 'Image', function($id){ return Factory::create($id, 'image'); } )
        ];
    }


    /**
     * Convert to array
     *
     * @param $arr
     * @return array
     */
    public function toArray($arr ) {

        return (array)$arr;
    }


    /**
     * Check if file exists
     * @param $path
     * @return bool
     */
    public function fileExists($path ) {

        return substr($path, 0, 4) == 'http' || file_exists(BASE_URI . PUBLIC_DIR . $path);
    }


    /**
     * Generate blurhash
     * @return string
     */
    public function blurhash($image) {

        if( is_string($image) )
            $image = new Image($image);
        elseif( is_array($image) && isset($image['url']) )
            $image = new Image($image['url']);

        if( !$image instanceof Image )
            $image = new Image();

        return $image->getBlurhash();
    }


    /**
     * Generate transparent pixel base64 image
     * @param $w
     * @param $h
     * @return string
     */
    public function generatePixel($w = 1, $h = 1) {

        ob_start();

        if( $h == 0 )
            $h = $w;
        elseif( $w == 0 )
            $w = $h;

        $img = imagecreatetruecolor($w, $h);
        imagetruecolortopalette($img, false, 1);
        imagesavealpha($img, true);
        $color = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $color);
        imagepng($img, null, 9);
        imagedestroy($img);

        $imagedata = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($imagedata);
    }


    /**
     * Return resized image
     *
     * @param $image
     * @param $width
     * @param int $height
     * @param array $params
     * @return string
     */
    public function resize($image, $width, $height=0, $params=[])
    {
        if( is_string($image) )
            $image = new Image($image);
        elseif( is_array($image) && isset($image['url']) )
            $image = new Image($image['url']);

        if( !$image instanceof Image )
            $image = new Image();

        return $image->resize($width, $height, $params['ext']??null, $params);
    }


    /**
     * Return resized picture
     *
     * @param $image
     * @param $width
     * @param int $height
     * @param array $sources
     * @param bool $alt
     * @param string $loading
     * @param array $params
     * @return Markup
     */
    public function picture($image, $width, $height=0, $sources=[], $alt=false, $loading='lazy', $params=[])
    {
        if( is_array($alt) ){

            $params = $alt;
            $alt = $params['alt']??false;
        }

        if( is_array($loading) ){

            $params = $loading;
            $loading = $params['loading']??'lazy';
        }

        if( !is_array($params) )
            $params = [];

        if( is_string($image) && !empty($image) ){

            $image = new Image($image);
        }
        elseif( is_array($image) && !empty($image['url']??'') ){

            if( !$alt && isset($image['alt']) )
                $alt = $image['alt'];

            $image = new Image($image['url']);
        }

        if( !$image instanceof Image ){

            if( !$height )
                $height = $width;

            $html = '<picture><img src="'.$this->generatePixel($width, $height).'" class="placeholder" width="'.$width.'" height="'.$height.'" alt="'.htmlspecialchars($alt, ENT_QUOTES, 'UTF-8').'"/></picture>';
        }
        else{

            $html = $image->picture($width, $height, $sources, $alt, $loading, $params);
        }

        if( $params['figure']??false ){

            $html = '<figure>'.$html;

            if( !empty($image->caption) and $image->caption != 'default' )
                $html  .= '<figcaption>'.$image->caption.'</figcaption>';

            $html .= '</figure>';
        }

        return new \Twig\Markup($html, 'UTF-8');
    }


    /**
     * Return resized picture
     *
     * @param $image
     * @param $width
     * @param int $height
     * @param array $sources
     * @param bool $alt
     * @param string $loading
     * @param array $params
     * @return Markup
     */
    public function figure($image, $width, $height=0, $sources=[], $alt=false, $loading='lazy', $params=[])
    {
        if( !is_array($params) )
            $params = [];

        $params['figure'] = true;

        return $this->picture($image, $width, $height, $sources, $alt, $loading, $params);
    }


    /**
     * Return html
     *
     * @param $text
     * @return Markup
     */
    public function doShortcode($text)
    {
        $html = do_shortcode($text);

        return new \Twig\Markup($html, 'UTF-8');
    }


    /**
     * Return function echo
     * @param $function
     * @param array $args
     * @return string
     */
    private function getOutput($function, $args=[])
    {
        ob_start();
        call_user_func_array($function, $args);

        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }


    /**
     * @param object|int|string $term
     * @param string $taxonomy
     * @param bool|string $meta
     * @return false|string
     */
    public function getTermLink( $term, $taxonomy = 'category', $meta=false )
    {

        if( $meta ){

            $args = array(
                'meta_query' => array(
                    array(
                        'key'       => $meta,
                        'value'     => $term,
                        'compare'   => 'LIKE'
                    )
                ),
                'number'  => 1,
                'taxonomy'  => $taxonomy,
            );

            $terms = get_terms( $args );

            if( count($terms) )
                $term = $terms[0];
        }


        $link = get_term_link($term, $taxonomy);

        if( !is_string($link) )
            return false;

        return $link;
    }


    /**
     * @param $content
     * @return array
     */
    public function more( $content )
    {
        if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
            if ( has_block( 'more', $content ) ) {
                // Remove the core/more block delimiters. They will be left over after $content is split up.
                $content = preg_replace( '/<!-- \/?wp:more(.*?) -->/', '', $content );
            }

            $content = explode( $matches[0], $content, 2 );
        } else {
            $content = array( $content );
        }

        foreach ($content as &$paragraph)
            $paragraph = force_balance_tags($paragraph);

        return $content;
    }


    /**
     * @param $function_name
     * @return mixed
     */
    public function execFunction( $function_name )
    {
        $args = func_get_args();

        array_shift($args);

        if ( is_string($function_name) )
            $function_name = trim($function_name);

        return call_user_func_array($function_name, ($args));
    }


    /**
     * @param $action_name
     * @param mixed ...$args
     * @return void
     */
    public function doAction( $action_name, ...$args )
    {
        do_action_ref_array( $action_name, $args );
    }


    /**
     * @param $page
     * @param bool $by
     * @return false|string
     */
    public function getPermalink( $page, $by=false )
    {
        switch ( $by ){

            case 'state':

                if( !function_exists('get_page_by_state') )
                    return false;

                $page = get_page_by_state($page);
                break;

            case 'path':

                $page = get_page_by_path($page);
                break;

            case 'title':

                $page = get_page_by_title($page);
                break;

            case 'slug':

                if( !is_array($page) or count($page) != 2 )
                    return false;

                $post_ids = get_posts([
                    'name'   => $page[0],
                    'post_type'   => $page[1],
                    'numberposts' => 1,
                    'fields' => 'ids'
                ]);

                if( count($post_ids) )
                    $page = $post_ids[0];
        }

        if( $page ){

            $link = get_permalink($page);

            if( !is_string($link) )
                return false;

            return $link;
        }
        else
            return false;
    }


    /**
     * @param $image
     * @param bool $params
     * @return Image
     */
    public function placeholder($image, $params=false)
    {
        if(!$image instanceof Image)
            return new Image();

        return $image;
    }


    /**
     * @param $w
     * @param $h
     * @return string
     */
    public function generatePlaceholder($w, $h)
    {
        $image = new Image();

        return $image->placeholder($w, $h);
    }
}
