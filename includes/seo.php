<?php
    global $post;

    $post_id = isset($post) ? $post->ID : NULL;
    $post_title = isset($post) ? $post->post_title : NULL;
    $publish_date = isset($post) ? $post->post_date : NULL;
    $modified_date = isset($post) ? $post->post_modified : NULL;

    $seo_post_title = get_field('seo_title', $post_id);
    $seo_post_description = get_field('seo_description', $post_id);
    $seo_post_image = get_field('seo_image', $post_id);

    $site_name = get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description' );
    $site_url = get_bloginfo( 'url' );

    $seo_title = get_field('title', 'seo-settings');
    $seo_description = get_field('description', 'seo-settings');
    $seo_image = get_field('image', 'seo-settings');

    $seo_title = isset($seo_title) && $seo_title !== '' ? $seo_title : $site_name;
    $seo_description = isset($seo_description) && $seo_description !== '' ? $seo_description : $site_description;
    $seo_url = $site_url;

    if($seo_post_title && isset($seo_post_title) && $seo_post_title !== ''){
        $seo_title = $seo_post_title;
    }else{
        if(!is_front_page()){
            $seo_title = $post_title;
        } 
    }

    if($seo_post_description && isset($seo_post_description) && $seo_post_description !== ''){
        $seo_description = $seo_post_description;
    }else{
        if(!is_front_page()){
            $seo_description = get_post_excerpt($post_id);
        }
    }

    if($seo_post_image && isset($seo_post_image) && $seo_post_image !== ''){
        $seo_image = $seo_post_image;
    }
?>

<title><?php echo $seo_title; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />

<meta name="description" content="<?php echo $seo_description; ?>" />

<link rel="canonical" href="<?php echo $seo_url; ?>"/>

<meta property="og:locale" content="<?php echo $curlocale; ?>" />
<meta property="og:type" content="website" />
<meta property="og:title" content="<?php echo $seo_title; ?>"/>
<meta property="og:description" content="<?php echo $seo_description; ?>" />
<meta property="og:url" content="<?php echo $seo_url; ?>"/>
<meta property="og:image" content="<?php echo $seo_image; ?>" />
<meta property="og:site_name" content="<?php echo $site_name; ?>" />

<script type="application/ld+json">
    {"@context":"https://schema.org",
        "@graph":[
            {"@type":"WebPage","@id":"<?php echo $seo_url; ?>",
                "url":"<?php echo $seo_url; ?>",
                "name":"<?php echo $seo_title; ?>",
                "isPartOf":{"@id":"<?php echo $seo_url; ?>/#website"},
                "datePublished":"<?php echo $publish_date; ?>",
                "dateModified":"<?php echo $modified_date; ?>",
                "description":"<?php echo $seo_description; ?>",
                "breadcrumb":{"@id":"<?php echo $seo_url; ?>/#breadcrumb"},
                "inLanguage":"<?php echo $curlocale; ?>",
                "potentialAction":[{"@type":"ReadAction","target":["<?php echo $seo_url; ?>"]}]},
                {"@type":"BreadcrumbList","@id":"<?php echo $seo_url; ?>/#breadcrumb",
                    "itemListElement":[{"@type":"ListItem","position":1,"name":"<?php echo $post_title; ?>"}]},
                    {"@type":"WebSite","@id":"<?php echo $seo_url; ?>/#website","url":"<?php echo $seo_url; ?>",
                        "name":"<?php echo $site_name; ?>",
                        "description":"<?php echo $site_description; ?>",
                        "potentialAction":[{"@type":"SearchAction",
                            "target":{"@type":"EntryPoint",
                                "urlTemplate":"<?php echo $seo_url; ?>/?s={search_term_string}"},
                                "query-input":"required name=search_term_string"}],
                                "inLanguage":"<?php echo $curlocale; ?>"}]}
</script>