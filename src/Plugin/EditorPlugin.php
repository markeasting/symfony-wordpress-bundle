<?php

namespace Metabolism\WordpressBundle\Plugin;


/**
 * Class
 */
class EditorPlugin
{

    public function adminHead()
    {

        $entrypoints = BASE_URI . PUBLIC_DIR . '/build/entrypoints.json';

        if (file_exists($entrypoints)) {

            $entrypoints = json_decode(file_get_contents($entrypoints), true);

            if ($entrypoints = $entrypoints['entrypoints']['backoffice'] ?? false) {

                foreach ($entrypoints['js'] ?? [] as $file)
                    echo '<script src="' . $file . '"></script>'; foreach ($entrypoints['css'] ?? [] as $file)
                    echo '<link rel="stylesheet" href="' . $file . '" media="all"/>';
            }

            echo "\n";
        }

        echo "<style>.form-table.permalink-structure, .form-table.permalink-structure+h2{ display:none }</style>";
    }

    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_head', [$this, 'adminHead']);
        }
    }
}
