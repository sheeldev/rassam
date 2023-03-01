<?php
require_once(SITE_ROOT . 'application/config.php');
//
if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}

$sheel->template->meta['jsinclude'] = array(
    'header' => array(
        'vendor/jquery_1.12.4',
        'vendor/growl'
    ),
    'footer' => array(
        'vendor/bootstrap_3.3.5',
        'vendor/jquery_slides',
        'vendor/jquery_fancybox',
        'vendor/owl_carousel',
        'vendor/jquery_jcarousellite',
        'vendor/jquery_elevatezoom',
        'vendor/TimeCircles',
        'vendor/theme',
        'vendor/timeline',
        'vendor/jquery_ui'
    )
);
$sheel->template->meta['cssinclude'] = array(
    'vendor' => array(
        'bootstrap3.3.5',
        'balloon',
        'font-awesome',
        'glyphicons',
        'growl',
        'linear-icon',
        'font-elegant',
        'animations',
        'bootstrap-theme',
        'jquery.fancybox',
        'jquery-ui',
        'owl.carousel',
        'owl.transitions',
        'owl.theme',
        'hover',
        'color'
    ),
    'general',
    'theme',
    'timeline'
);

$sheel->template->meta['navcrumb'] = array(
    HTTPS_SERVER => '{_homepage}'
);

define('LOCATION', 'main');
if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'content' and isset($sheel->GPC['go'])) {
    /*
     * Redirects to the URL in new Window
     * i.e. if www.website.com/go/contact
     * it will open the contact page in new window
     */
    switch ($sheel->GPC['go']) {
        case 'terms': {
                $sql = $sheel->db->query("
			SELECT seourl
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
				AND ispublished = '1'
				AND isterms = '1'
			LIMIT 1
		");
                if ($sheel->db->num_rows($sql) > 0) {
                    $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                    refresh(HTTPS_SERVER . 'content/' . $res['seourl'] . '.html');
                    exit();
                }
                break;
            }
        case 'privacy': {
                $sql = $sheel->db->query("
			SELECT seourl
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
				AND ispublished = '1'
				AND isprivacy = '1'
			LIMIT 1
		");
                if ($sheel->db->num_rows($sql) > 0) {
                    $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                    refresh(HTTPS_SERVER . 'content/' . $res['seourl'] . '.html');
                    exit();
                }
                break;
            }
        case 'cookies': {
                $sql = $sheel->db->query("
			SELECT seourl
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
				AND ispublished = '1'
				AND iscookies = '1'
			LIMIT 1
		");
                if ($sheel->db->num_rows($sql) > 0) {
                    $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                    refresh(HTTPS_SERVER . 'content/' . $res['seourl'] . '.html');

                    exit();
                }
                break;
            }
    }
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'content' and isset($sheel->GPC['view'])) {
    /*
     * if its a CMS page, then open the
     * page in view: main_custom_page.html
     */
    $sheel->show['nobreadcrumb'] = true;
    $sheel->show['slimheader'] = $sheel->show['slimfooter'] = false;
    $sheel->show['widescreen'] = true;
    $sheel->show['fluidscreen'] = true;
    $sheel->template->meta['cssinclude'][] = 'landing_page';
    $sheel->template->meta['area'] = 'landing_page';
    $view = $sheel->GPC['view'];

    $nav = '';

    // var_dump('Jamil: ' + $sheel->GPC['cmd']); die();

    if (!empty($view)) {
        $sql = $sheel->db->query("
			SELECT title, membersonly, seourl, parentid
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
				AND ispublished = '1'
				AND sidebar = '1'
			ORDER BY sort ASC
		");
        if ($sheel->db->num_rows($sql) > 0) {

            $nav .= '<ul class="unordered-list nostyle spacing-base"><div aria-live="polite">';
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $name = 'nav_' . $res['seourl'];
                $$name = HTTPS_SERVER . 'content/' . $res['seourl'] . '.html';

                $nav .= '<li' . (($res['parentid'] > 0) ? ' class="plr-15"' : '') . '><span class="list-item"><a class="link-normal" href="' . HTTPS_SERVER . 'content/' . $res['seourl'] . '.html"><h4 class="size-small spacing-top-mini text-bold' . ((!empty($view) and $view == $res['seourl']) ? ' a_active' : '') . '">' . o($res['title']) . '</h4></a></span></li>';
            }
            $nav .= '</div>
			</ul>';
        }
        $sql = $sheel->db->query("
			SELECT title, seourl, subtitle, description, description_html, keywords, uniqueviews, publishdate, lastupdate, userid, membersonly, isterms, isprivacy, iscookies, showdate
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
				AND ispublished = '1'
				AND seourl = '" . $sheel->db->escape_string($view) . "'
			LIMIT 1
		");
        if ($sheel->db->num_rows($sql) > 0) {
            $res = $sheel->db->fetch_array($sql, DB_ASSOC);
            if ($res['membersonly'] and !isset($_SESSION['sheeldata']['user']['userid'])) {
                refresh(HTTPS_SERVER . 'signin/?redirect=/content/' . $view . '.html');
                exit();
            }
            if ($res['lastupdate'] != '0000-00-00 00:00:00' and $res['showdate']) {
                $sheel->show['topdynamic'] = true;
                $sheel->template->meta['topdynamic'] = '<div class="sr-left-nav"><span id="verbosetext" class="black"><span class="onlymobile optionsp ' . (($sheel->config['template_textdirection'] == 'ltr') ? 'left' : 'right') . ' mlr-6" id="optionsp"><a href="javascript:;"><img src="' . $sheel->config['imgcdn'] . 'v5/ico_mmd.png" border="0" alt="" /></a></span><a class="top-cat-link" href="javascript:;">{_last_updated}</a>: <a class="top-cat-link" href="javascript:;">' . $sheel->common->print_date($res['lastupdate'], 'M d, Y h:i A', 1, 0) . '</a></span></div><div class="sr-right-nav onlydesktop"></div>';
            }
            if ($res['isterms']) {
            } else if ($res['isprivacy']) {
            } else if ($res['iscookies']) {
            }

            if ($sheel->GPC['ismobile'] == 1) {
                $sheel->template->meta['mobile'] = '1';
                $nav1 = '.';
            } else {
                $nav1 = '<div class="brc-wrap title16"><a class="white" href="' . HTTPS_SERVER . '">Home</a><a class="color" href="' . HTTPS_SERVER . 'content/' . $res['seourl'] . '.html">' . $res['title'] . '</a></div>';
            }

            $vars = array(
                'custom_page_title' => o($res['title']),
                'subtitle' => o($res['subtitle']),
                'content' => ((!empty($res['description_html'])) ? $res['description_html'] : $res['description']),
                'navmobile' => $nav1
            );

            $sheel->template->meta['areatitle'] = '{_content} <div class="smaller">' . o($res['title']) . '</div>';
            $sheel->template->meta['pagetitle'] = o($res['title']) . ' | ' . SITE_NAME;
            $sheel->template->meta['keywords'] = o($res['keywords']);
            $sheel->template->meta['description'] = $sheel->shorten(o(strip_tags($sheel->bbcode->strip_bb_tags(trim(preg_replace("/[\\n\\r\\t]+/", ' ', $res['subtitle']))))), 200);
            $sheel->template->fetch('main', 'main_custom_landing_page.html');
            $sheel->template->parse_hash(
                'main',
                array(
                    'ilpage' => $sheel->ilpage
                )
            );
            $sheel->template->pprint('main', $vars);

            exit();
        }
    }
    refresh(HTTPS_SERVER);
    exit();
} else {
    $sheel->template->meta['area'] = 'landing_page';
    $sheel->show['widescreen'] = true;
    $sheel->show['fluidscreen'] = false;
    $sheel->show['nobreadcrumb'] = $sheel->show['categorynav'] = true;
    $sheel->template->meta['areatitle'] = '{_main_menu}';
    $sheel->template->meta['pagetitle'] = '{_template_metatitle} | ' . SITE_NAME;
    $sheel->template->meta['pagetitle'] = 'The smart way to sell and monitor products on Amazon | ' . SITE_NAME;
    $sheel->template->meta['description'] = '{_template_metadescription}';
    $sheel->template->meta['keywords'] = '{_template_metakeywords}';
    $sheel->template->meta['navcrumb'] = array();
    $sheel->template->meta['navcrumb'][""] = '{_marketplace}';
    $sheel->template->meta['cssinclude'][] = 'landing_page';
    $landingpageheros = array();
    $hpaurl = $heroimagemaps = '';
    if (($landingpageheros = $sheel->cache->fetch('landingpageheros')) === false) {
        $landingpageheros = $sheel->hero->fetch_heros('landingpage');
        $sheel->cache->store('landingpageheros', $landingpageheros);
    }

    if (count($landingpageheros) > 0) {
        foreach ($landingpageheros as $key => $value) {
            if (!empty($value['imagemap'])) {
                $heroimagemaps .= str_replace('{id}', $value['id'], $value['imagemap']);
            }
        }
    }

    $loops = array(
        'landingpageheros' => $landingpageheros
    );

    $sql = $sheel->db->query("
			SELECT content, date, imagename, visible
			FROM " . DB_PREFIX . "announcements
			WHERE visible = '1'
			ORDER BY date ASC");
    $ann = '';
    $annimage = '';

    if ($sheel->db->num_rows($sql) > 0) {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            $ann = $res['content'];
            $annimage = $res['imagename'];
        }
    }

    $vars = array(
        'name' => 'Sheel Store',
        'home_link' => SITE_ROOT,
        'announcement' => $ann,
        'announcement_image' => $annimage,
        'heroimagemaps' => $heroimagemaps

    );
    $sheel->template->fetch('main', 'main_landing_page.html');
    $sheel->template->parse_loop('main', $loops, false);
    $sheel->template->pprint('main', $vars);
    exit();
}

?>