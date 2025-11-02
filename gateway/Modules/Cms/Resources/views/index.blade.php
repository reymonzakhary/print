@php
    ob_start();
    $token = csrf_token();
    $csrf = "<meta name='csrf-token' content='{$token}' />";

    $tenant  =request()->tenant->uuid;
    $matched = [];
    $html = htmlspecialchars_decode(optional(optional($resource)->template)->content);

    preg_match_all('/(src|href)="([^"]+\.(js|css|png|jpg|jpeg|gif|woff2|woff|html|svg|ico))"/', $html, $source);
    $html = preg_replace('/@csrf/', $csrf, $html);

    preg_match('/\[\[Tree(.*?)\]\]/', $html, $tree);
    preg_match_all('/\[\[(.*?)\]\]/',  $html, $matched);

    //preg_match_all('/\[\[*/')

    foreach($source[2] as $src) {
        if(str_contains($src, 'assets')) {
            if(file_exists(public_path("storage/{$tenant}/{$src}"))) {
                $html = str_replace('"'.$src.'"', '"'.url("storage/{$tenant}/{$src}").'"', $html);
            }
        }else{
            if(file_exists(public_path("storage/{$tenant}/assets/{$src}"))) {
                $html = str_replace('"'.$src.'"', '"'.url("storage/{$tenant}/assets/{$src}").'"', $html);
            }
        }
    }
/*    if(count($matched[1]) === 0) {
        return '';
    }*/


/*
    $q  = explode('&',trim(str_replace(['`', '?'],['', ''],$tree[1])));
    dd($q);
    $query = [];
    foreach($q as $qu) {
        if($qu) {
            $key = explode('=', $qu);
            if(count($key) > 0) {
                if(in_array($key[0], ['start', 'depth'])){
                    $query[$key[0]] = (int)$key[1];
                }
            }
        }
    }
*/
    //dd($query);
    preg_match_all('/\*(.*?)\?/',  $html, $tags);
    $content = $resource->content;
    $htm = preg_replace_callback('/\[\[(.*?)\]\]/', function($matches) use ($content) {
        if($content) {
            foreach($content as $v) {
                preg_match('/\*(.*?)\?/', $matches[1], $tag);
                if(count($tag) > 0 && in_array($tag[1], array_flip($v))) {
                    return $v[$tag[1]];
                }
            }
        }
    },$html);

    ob_end_clean();
    echo htmlspecialchars_decode($htm);
@endphp
