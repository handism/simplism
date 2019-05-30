<?php
// サイト設定
/* twitterのID */
$twitter_id = '@xxxxx';
/* 記事ページ以外のOGP用の画像のURL */
$ogp_img_url = 'https://xxxxx.xxx/xxxxx.jpg';

// HTML5に対応する
add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

// 記事ページのページネーションを有効にする
add_action('genesis_entry_footer', 'genesis_prev_next_post_nav');

// WordPressのアイコンフォントを使えるようにする
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('dashicons');
});

// パンくずリストの不要な文字の除去
add_filter('genesis_breadcrumb_args', function($args) {
	$args['labels']['prefix'] = '';
	$args['labels']['author'] = '';
	$args['labels']['category'] = '';
	$args['labels']['tag'] = '';
	$args['labels']['date'] = '';
	$args['labels']['search'] = '';
	$args['labels']['tax'] = '';
	$args['labels']['post_type'] = '';
	$args['labels']['404'] = 'Not found: ';
    return $args;
});

// 続きを読むの文字を変更
add_filter('the_content_more_link', function() {
	return '<a href="' . get_permalink() . '">[続きを読む]</a>';
});

// ページネーションの文字を変更
add_filter('genesis_next_link_text', function() {
    return '次';
});
add_filter('genesis_prev_link_text', function() {
    return '前';
});

// サイト内検索フォームの文字を変更
add_filter('genesis_search_text', function() {
	return esc_attr('サイト内検索');
});

// twitterとブログ村向けのOGP
add_action('wp_head', function() {
	if (is_single()) {
		$tc_image = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');
		echo '<meta name="twitter:card" content="summary_large_image" />' .
			 '<meta name="twitter:site" content="' . $twitter_id . '" />' .
			 '<meta property="og:locale" content="ja_JP">' .
			 '<meta property="og:title" content="' . get_the_title() . '" />' .
			 '<meta property="og:type" content="article" />' .
			 '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />' .
		     '<meta property="og:description" content="' . get_the_excerpt() . '" />' .
		     '<meta property="og:url" content="' . get_permalink() . '" />' .
		     '<meta property="og:image" content="' . $tc_image[0] . '" />';
	} else {
		echo '<meta name="twitter:card" content="summary_large_image" />' .
            '<meta name="twitter:site" content="' . $twitter_id . '" />' .
			 '<meta property="og:locale" content="ja_JP">' .
			 '<meta property="og:title" content="' . get_bloginfo() . '" />' .
			 '<meta property="og:type" content="blog" />' .
			 '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />' .
		     '<meta property="og:description" content="' . get_bloginfo('description') . '" />' .
		     '<meta property="og:url" content="' . get_home_url() . '" />' .
		     '<meta property="og:image" content="' . $ogp_img_url . '" />';
	}
});

// 目次自動生成
add_filter('the_content', function($the_content) {
    if (is_single()) {
        $tag = '/^<h[2-6].*?>(.+?)<\/h[2-6]>$/im';
        if (preg_match_all($tag, $the_content, $tags)) {
            $idpattern = '/id *\= *["\'](.+?)["\']/i';
            $table_of_contents = '<div class="table_of_contents"><p class="toc_title">目次</p><ul>';
            $idnum = 1;
            $nest = 0;
            $nestTag = array();
            for ($i = 0 ; $i < count($tags[0]) ; $i++) {
                if ( ! preg_match_all($idpattern, $tags[0][$i], $idstr) ) {
                    $idstr[1][0] = 'a'.$idnum++; 
                    $the_content = preg_replace( "/".str_replace('/', '\/' ,$tags[0][$i])."/", preg_replace('/(^<h[2-6])/i', '${1} id="' . $idstr[1][0] . '" ' , $tags[0][$i]), $the_content,1);
                }
                $table_of_contents .= '<li><a href="#' . $idstr[1][0] . '">' . $tags[1][$i] .'</a>';
 
                if ($i+1 >= count($tags[0])) {
                    $table_of_contents .= '</li>';
                } else {
                    preg_match_all('/^<h([2-6])/i' , $tags[0][$i] , $match1);
                    preg_match_all('/^<h([2-6])/i' , $tags[0][$i+1], $match2);
                    if ($match1[1][0] < $match2[1][0]) {
                        $table_of_contents .= '<ul>';
                        $nestTag[] = $match1[1][0];
                        $nest++;
                    } else if ($match1[1][0] == $match2[1][0]) {
                        $table_of_contents .= '</li>';
                    } else {
                        while (count($nestTag) > 0 && $nestTag[count($nestTag)-1] >= $match2[1][0]) {
                            $table_of_contents .= '</li></ul>';
                            array_splice($nestTag,count($nestTag)-1,1);
                            $nest--;
                        }
                        $table_of_contents .= '</li>';
                    }
                }
            }
 
            for (; $nest > 0 ; $nest--) {
                $table_of_contents .= '</ul></li>';
            }
 
            $table_of_contents .= '</ul></div>';
 
            if ($tags[0][0]) {
                $the_content = preg_replace('/(^<h[2-6].*?>.+?<\/h[2-6]>$)/im', $table_of_contents.'${1}', $the_content,1);
            }
        }
    }
    return $the_content;
});
