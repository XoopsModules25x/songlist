<?php

xoops_loadLanguage('user');

/**
 * Get {@link XoopsThemeForm} for adding/editing fields
 *
 * @param object $field  {@link ProfileField} object to get edit form for
 * @param mixed  $action URL to submit to - or false for $_SERVER['PHP_SELF']
 *
 * @return object
 */
function songlist_getFieldForm(&$field, $action = false)
{
    if (false === $action) {
        $action = $_SERVER['PHP_SELF'];
    }

    xoops_loadLanguage('forms', 'songlist');

    $title = $field->isNew() ? sprintf(_FRM_SONGLIST_FIELDS_ADD, _FRM_SONGLIST_FIELDS_FIELD) : sprintf(_FRM_SONGLIST_FIELDS_EDIT, _FRM_SONGLIST_FIELDS_FIELD);

    xoops_load('XoopsFormLoader');

    $form = new \XoopsThemeForm($title, 'form', $action, 'post', true);

    $form->addElement(new \XoopsFormText(_FRM_SONGLIST_FIELDS_TITLE, 'field_title', 35, 255, $field->getVar('field_title', 'e')));
    $form->addElement(new \XoopsFormTextArea(_FRM_SONGLIST_FIELDS_DESCRIPTION, 'field_description', $field->getVar('field_description', 'e')));

    if (!$field->isNew()) {
        $fieldcid = $field->getVar('cids');
    } else {
        $fieldcid = [1 => 0];
    }
    $categoryHandler = xoops_getModuleHandler('category', 'songlist');
    $cat_select      = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_CATEGORY, 'cids', $fieldcid, 7, true);
    $cat_select->addOption(0, _FRM_SONGLIST_FIELDS_DEFAULT);
    foreach ($categoryHandler->getObjects(null, true) as $cid => $category) {
        $cat_select->addOption($cid, $category->getVar('name'));
    }
    $form->addElement($cat_select);
    $form->addElement(new \XoopsFormText(_FRM_SONGLIST_FIELDS_WEIGHT, 'field_weight', 10, 10, $field->getVar('field_weight', 'e')));
    if ($field->getVar('field_config') || $field->isNew()) {
        if (!$field->isNew()) {
            $form->addElement(new \XoopsFormLabel(_FRM_SONGLIST_FIELDS_NAME, $field->getVar('field_name')));
            $form->addElement(new \XoopsFormHidden('id', $field->getVar('field_id')));
        } else {
            $form->addElement(new \XoopsFormText(_FRM_SONGLIST_FIELDS_NAME, 'field_name', 35, 255, $field->getVar('field_name', 'e')));
        }

        //autotext and theme left out of this one as fields of that type should never be changed (valid assumption, I think)
        $fieldtypes = [
            'checkbox'     => _FRM_SONGLIST_FIELDS_CHECKBOX,
            'date'         => _FRM_SONGLIST_FIELDS_DATE,
            'datetime'     => _FRM_SONGLIST_FIELDS_DATETIME,
            'longdate'     => _FRM_SONGLIST_FIELDS_LONGDATE,
            'group'        => _FRM_SONGLIST_FIELDS_GROUP,
            'group_multi'  => _FRM_SONGLIST_FIELDS_GROUPMULTI,
            'language'     => _FRM_SONGLIST_FIELDS_LANGUAGE,
            'radio'        => _FRM_SONGLIST_FIELDS_RADIO,
            'select'       => _FRM_SONGLIST_FIELDS_SELECT,
            'select_multi' => _FRM_SONGLIST_FIELDS_SELECTMULTI,
            'textarea'     => _FRM_SONGLIST_FIELDS_TEXTAREA,
            'dhtml'        => _FRM_SONGLIST_FIELDS_DHTMLTEXTAREA,
            'textbox'      => _FRM_SONGLIST_FIELDS_TEXTBOX,
            'timezone'     => _FRM_SONGLIST_FIELDS_TIMEZONE,
            'yesno'        => _FRM_SONGLIST_FIELDS_YESNO
        ];

        $element_select = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_TYPE, 'field_type', $field->getVar('field_type', 'e'));
        $element_select->addOptionArray($fieldtypes);

        $form->addElement($element_select);

        switch ($field->getVar('field_type')) {
            case 'textbox':
                $valuetypes = [
                    XOBJ_DTYPE_ARRAY   => _FRM_SONGLIST_FIELDS_ARRAY,
                    XOBJ_DTYPE_EMAIL   => _FRM_SONGLIST_FIELDS_EMAIL,
                    XOBJ_DTYPE_INT     => _FRM_SONGLIST_FIELDS_INT,
                    XOBJ_DTYPE_FLOAT   => _FRM_SONGLIST_FIELDS_FLOAT,
                    XOBJ_DTYPE_DECIMAL => _FRM_SONGLIST_FIELDS_DECIMAL,
                    XOBJ_DTYPE_TXTAREA => _FRM_SONGLIST_FIELDS_TXTAREA,
                    XOBJ_DTYPE_TXTBOX  => _FRM_SONGLIST_FIELDS_TXTBOX,
                    XOBJ_DTYPE_URL     => _FRM_SONGLIST_FIELDS_URL,
                    XOBJ_DTYPE_OTHER   => _FRM_SONGLIST_FIELDS_OTHER
                ];

                $type_select = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_VALUETYPE, 'field_valuetype', $field->getVar('field_valuetype', 'e'));
                $type_select->addOptionArray($valuetypes);
                $form->addElement($type_select);
                break;

            case 'select':
            case 'radio':
                $valuetypes = [
                    XOBJ_DTYPE_ARRAY   => _FRM_SONGLIST_FIELDS_ARRAY,
                    XOBJ_DTYPE_EMAIL   => _FRM_SONGLIST_FIELDS_EMAIL,
                    XOBJ_DTYPE_INT     => _FRM_SONGLIST_FIELDS_INT,
                    XOBJ_DTYPE_FLOAT   => _FRM_SONGLIST_FIELDS_FLOAT,
                    XOBJ_DTYPE_DECIMAL => _FRM_SONGLIST_FIELDS_DECIMAL,
                    XOBJ_DTYPE_TXTAREA => _FRM_SONGLIST_FIELDS_TXTAREA,
                    XOBJ_DTYPE_TXTBOX  => _FRM_SONGLIST_FIELDS_TXTBOX,
                    XOBJ_DTYPE_URL     => _FRM_SONGLIST_FIELDS_URL,
                    XOBJ_DTYPE_OTHER   => _FRM_SONGLIST_FIELDS_OTHER
                ];

                $type_select = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_VALUETYPE, 'field_valuetype', $field->getVar('field_valuetype', 'e'));
                $type_select->addOptionArray($valuetypes);
                $form->addElement($type_select);
                break;
        }

        //$form->addElement(new \XoopsFormRadioYN(_FRM_SONGLIST_FIELDS_NOTNULL, 'field_notnull', $field->getVar('field_notnull', 'e') ));

        if ('select' === $field->getVar('field_type') || 'select_multi' === $field->getVar('field_type') || 'radio' === $field->getVar('field_type') || 'checkbox' === $field->getVar('field_type')) {
            $options = $field->getVar('field_options');
            if (count($options) > 0) {
                $remove_options          = new \XoopsFormCheckBox(_FRM_SONGLIST_FIELDS_REMOVEOPTIONS, 'removeOptions');
                $remove_options->columns = 3;
                asort($options);
                foreach (array_keys($options) as $key) {
                    $options[$key] .= "[{$key}]";
                }
                $remove_options->addOptionArray($options);
                $form->addElement($remove_options);
            }

            $option_text = "<table  cellspacing='1'><tr><td width='20%'>" . _FRM_SONGLIST_FIELDS_KEY . '</td><td>' . _FRM_SONGLIST_FIELDS_VALUE . '</td></tr>';
            for ($i = 0; $i < 3; ++$i) {
                $option_text .= "<tr><td><input type='text' name='addOption[{$i}][key]' id='addOption[{$i}][key]' size='15'></td><td><input type='text' name='addOption[{$i}][value]' id='addOption[{$i}][value]' size='35'></td></tr>";
                $option_text .= "<tr height='3px'><td colspan='2'> </td></tr>";
            }
            $option_text .= '</table>';
            $form->addElement(new \XoopsFormLabel(_FRM_SONGLIST_FIELDS_ADDOPTION, $option_text));
        }
    }

    if ($field->getVar('field_edit')) {
        switch ($field->getVar('field_type')) {
            case 'textbox':
            case 'textarea':
            case 'dhtml':
                $form->addElement(new \XoopsFormText(_FRM_SONGLIST_FIELDS_MAXLENGTH, 'field_maxlength', 35, 35, $field->getVar('field_maxlength', 'e')));
                $form->addElement(new \XoopsFormTextArea(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;

            case 'checkbox':
            case 'select_multi':
                $def_value = null != $field->getVar('field_default', 'e') ? unserialize($field->getVar('field_default', 'n')) : null;
                $element   = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $def_value, 8, true);
                $options   = $field->getVar('field_options');
                asort($options);
                // If options do not include an empty element, then add a blank option to prevent any default selection
                if (!in_array('', array_keys($options))) {
                    $element->addOption('', _NONE);
                }
                $element->addOptionArray($options);
                $form->addElement($element);
                break;

            case 'select':
            case 'radio':
                $def_value = null != $field->getVar('field_default', 'e') ? $field->getVar('field_default') : null;
                $element   = new \XoopsFormSelect(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $def_value);
                $options   = $field->getVar('field_options');
                asort($options);
                // If options do not include an empty element, then add a blank option to prevent any default selection
                if (!in_array('', array_keys($options))) {
                    $element->addOption('', _NONE);
                }
                $element->addOptionArray($options);
                $form->addElement($element);
                break;

            case 'date':
                $form->addElement(new \XoopsFormTextDateSelect(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', 15, $field->getVar('field_default', 'e')));
                break;

            case 'longdate':
                $form->addElement(new \XoopsFormTextDateSelect(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', 15, strtotime($field->getVar('field_default', 'e'))));
                break;

            case 'datetime':
                $form->addElement(new \XoopsFormDateTime(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', 15, $field->getVar('field_default', 'e')));
                break;

            case 'yesno':
                $form->addElement(new \XoopsFormRadioYN(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;

            case 'timezone':
                $form->addElement(new \XoopsFormSelectTimezone(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;

            case 'language':
                $form->addElement(new \XoopsFormSelectLang(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;

            case 'group':
                $form->addElement(new \XoopsFormSelectGroup(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', true, $field->getVar('field_default', 'e')));
                break;

            case 'group_multi':
                $form->addElement(new \XoopsFormSelectGroup(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', true, $field->getVar('field_default', 'e'), 5, true));
                break;

            case 'theme':
                $form->addElement(new \XoopsFormSelectTheme(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;

            case 'autotext':
                $form->addElement(new \XoopsFormTextArea(_FRM_SONGLIST_FIELDS_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
                break;
        }
    }

    $grouppermHandler = xoops_getHandler('groupperm');
    $searchable_types = [
        'textbox',
        'select',
        'radio',
        'yesno',
        'date',
        'datetime',
        'timezone',
        'language'
    ];
    if (in_array($field->getVar('field_type'), $searchable_types)) {
        $search_groups = $grouppermHandler->getGroupIds('songlist_search', $field->getVar('field_id'), $GLOBALS['songlistModule']->getVar('mid'));
        $form->addElement(new \XoopsFormSelectGroup(_FRM_SONGLIST_FIELDS_PROF_SEARCH, 'songlist_search', true, $search_groups, 5, true));
    }
    if ($field->getVar('field_edit') || $field->isNew()) {
        if (!$field->isNew()) {
            //Load groups
            $editable_groups = $grouppermHandler->getGroupIds('songlist_edit', $field->getVar('field_id'), $GLOBALS['songlistModule']->getVar('mid'));
        } else {
            $editable_groups = [];
        }
        $form->addElement(new \XoopsFormSelectGroup(_FRM_SONGLIST_FIELDS_PROF_EDITABLE, 'songlist_edit', false, $editable_groups, 5, true));
        $form->addElement($steps_select);
    }
    $form->addElement(new \XoopsFormHidden('op', 'save'));
    $form->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

    return $form;
}

/**
 * Get {@link XoopsThemeForm} for editing a user
 *
 * @param bool $action
 * @return object
 * @internal param object $user <a href='psi_element://XoopsUser'>XoopsUser</a> to edit to edit
 *
 */
function songlist_getUserSearchForm($action = false)
{
    xoops_loadLanguage('forms', 'songlist');

    if (false === $action) {
        $action = $_SERVER['PHP_SELF'];
    }
    if (empty($GLOBALS['xoopsConfigUser'])) {
        $configHandler              = xoops_getHandler('config');
        $GLOBALS['xoopsConfigUser'] = $configHandler->getConfigsByCat(XOOPS_CONF_USER);
    }

    $title = _FRM_SONGLIST_FIELDS_SEARCH;

    $form = new \XoopsThemeForm($title, 'search', $action, 'post', true);

    $songlistHandler = xoops_getModuleHandler('profile', 'objects');
    // Get fields
    $fields = $songlistHandler->loadFields();

    $gpermHandler  = xoops_getHandler('groupperm');
    $configHandler = xoops_getHandler('config');
    $groups        = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS];
    $moduleHandler = xoops_getHandler('module');
    $xoModule      = $moduleHandler->getByDirname('objects');
    $modid         = $xoModule->getVar('mid');

    // Get ids of fields that can be edited
    $gpermHandler = xoops_getHandler('groupperm');

    $editable_fields = $gpermHandler->getItemIds('songlist_search', $groups, $modid);

    $catHandler = xoops_getModuleHandler('category');

    $selcat = new \XoopsFormSelectForum('Forum', 'cid', (!empty($_REQUEST['cid'])) ? (int)$_REQUEST['cid'] : 0, 1, false, false, false, true);
    $selcat->setExtra(' onChange="window.location=\'' . XOOPS_URL . '/modules/objects/search.php?op=search&fct=form&cid=\'+document.search.cid.options[document.search.cid.selectedIndex].value"');

    $form->addElement($selcat, true);

    $categories = [];

    $criteria       = new \CriteriaCompo(new \Criteria('cid', (!empty($_REQUEST['cid'])) ? (int)$_REQUEST['cid'] : '0'), 'OR');
    $all_categories = $catHandler->getObjects($criteria, true, false);
    $count_fields   = count($fields);

    foreach (array_keys($fields) as $i) {
        if (in_array($fields[$i]->getVar('field_id'), $editable_fields)) {
            // Set default value for user fields if available
            $fieldinfo['element']  = $fields[$i]->getSearchElement();
            $fieldinfo['required'] = false;

            foreach ($fields[$i]->getVar('cids') as $catidid => $cid) {
                if (in_array($cid, array_keys($all_categories))) {
                    $key              = $all_categories[$cid]['cat_weight'] * $count_fields + $cid;
                    $elements[$key][] = $fieldinfo;
                    $weights[$key][]  = $fields[$i]->getVar('field_weight');
                    $categories[$key] = $all_categories[$cid];
                } elseif (in_array(0, $fields[$i]->getVar('cids'))) {
                    $key              = $all_categories[$cid]['cat_weight'] * $count_fields + $cid;
                    $elements[$key][] = $fieldinfo;
                    $weights[$key][]  = $fields[$i]->getVar('field_weight');
                    $categories[$key] = $all_categories[$cid];
                }
            }
        }
    }

    ksort($elements);
    foreach (array_keys($elements) as $k) {
        array_multisort($weights[$k], SORT_ASC, array_keys($elements[$k]), SORT_ASC, $elements[$k]);
        $title = isset($categories[$k]) ? $categories[$k]['cat_title'] : _OBJS_MF_DEFAULT;
        $desc  = isset($categories[$k]) ? $categories[$k]['cat_description'] : '';
        $form->addElement(new \XoopsFormLabel("<h3>{$title}</h3>", $desc), false);
        foreach (array_keys($elements[$k]) as $i) {
            $form->addElement($elements[$k][$i]['element'], $elements[$k][$i]['required']);
        }
    }

    $form->addElement(new \XoopsFormHidden('fct', 'objects'));
    $form->addElement(new \XoopsFormHidden('op', 'search'));
    $form->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

    return $form;
}

function songlist_import_get_form($as_array = false)
{
    xoops_loadLanguage('forms', 'songlist');

    $sform = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_IMPORT, 'import', $_SERVER['PHP_SELF'], 'post', true);
    $sform->setExtra("enctype='multipart/form-data'");

    $ele['op']      = new \XoopsFormHidden('op', 'import');
    $ele['fct']     = new \XoopsFormHidden('fct', 'upload');
    $ele['xmlfile'] = new \XoopsFormFile((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_UPLOAD_XML : ''), 'xmlfile', (1024 * 1024 * 1024 * 32));
    $ele['xmlfile']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_UPLOAD_XML_DESC : ''));
    $ele['file'] = new \XoopsFormSelect((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_EXISTING_XML : ''), 'file');
    $ele['file']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_EXISTING_XML_DESC : ''));
    $ele['file']->addOption('', '*********');
    xoops_load('XoopsLists');
    foreach (\XoopsLists::getFileListAsArray($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'])) as $file) {
        if ('xml' === substr($file, strlen($file) - 3, 3)) {
            $ele['file']->addOption($file, $file);
        }
    }
    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = [];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_importb_get_form($file, $as_array = false)
{
    xoops_loadLanguage('forms', 'songlist');

    $sform = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_ELEMENTS, 'elements', $_SERVER['PHP_SELF'], 'post', true);

    $filesize = filesize($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'] . $file));
    $mb       = floor($filesize / 1024 / 1024);
    if ($mb > 32) {
        set_ini('memory_limit', ($mb + 128) . 'M');
    }
    set_time_limit(3600);

    $i = 0;
    foreach (file($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'] . $_SESSION['xmlfile'])) as $data) {
        ++$i;
        if ($i < 20) {
            $line .= htmlspecialchars($data, ENT_QUOTES | ENT_HTML5) . ($i < 19 ? "\n" : '');
        }
    }

    $ele['op']      = new \XoopsFormHidden('op', 'import');
    $ele['fct']     = new \XoopsFormHidden('fct', 'import');
    $ele['example'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_EXAMPLE : ''), '<pre>' . $line . '</pre>');
    $ele['example']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_EXAMPLE_DESC : ''));
    $ele['collection'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_COLLECTION : ''), 'collection', 32, 128, 'collection');
    $ele['collection']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_COLLECTION_DESC : ''));
    $ele['record'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_RECORD : ''), 'record', 32, 128, 'record');
    $ele['record']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_RECORD_DESC : ''));
    $ele['genre'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_GENRES : ''), 'genre', 32, 128, 'genre');
    $ele['genre']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_GENRES_DESC : ''));
    $ele['voice'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_VOICE : ''), 'voice', 32, 128, 'voice');
    $ele['voice']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_VOICE_DESC : ''));
    $ele['category'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_CATEGORY : ''), 'category', 32, 128, 'category');
    $ele['category']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_CATEGORY_DESC : ''));
    $ele['artist'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_ARTIST : ''), 'artist', 32, 128, 'artist');
    $ele['artist']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_ARTIST_DESC : ''));
    $ele['album'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_ALBUM : ''), 'album', 32, 128, 'album');
    $ele['album']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_ALBUM_DESC : ''));
    $ele['songid'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_SONGID : ''), 'songid', 32, 128, 'songid');
    $ele['songid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_SONGID_DESC : ''));
    $ele['traxid'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TRAXID : ''), 'traxid', 32, 128, 'trackno');
    $ele['traxid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TRAXID_DESC : ''));
    $ele['title'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TITLE : ''), 'title', 32, 128, 'title');
    $ele['title']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TITLE_DESC : ''));
    $ele['lyrics'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_LYRICS : ''), 'lyrics', 32, 128, 'lyric');
    $ele['lyrics']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_LYRICS_DESC : ''));
    $ele['tags'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TAGS : ''), 'tags', 32, 128, 'tags');
    $ele['tags']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_TAGS_DESC : ''));
    $ele['mp3'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_MP3 : ''), 'mp3', 32, 128, 'mp3');
    $ele['mp3']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ELEMENT_MP3_DESC : ''));
    $extrasHandler = xoops_getModuleHandler('extras', 'songlist');
    $fields        = $extrasHandler->getFields(null);
    foreach ($fields as $field) {
        $ele[$field->getVar('field_name')] = new \XoopsFormText((false === $as_array ? $field->getVar('field_title') : ''), $field->getVar('field_name'), 32, 128, $field->getVar('field_name'));
        $ele[$field->getVar('field_name')]->setDescription((false === $as_array ? $field->getVar('field_description') : ''));
    }
    $ele['limiting'] = new \XoopsFormRadioYN((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_LIMITING : ''), 'limiting', true);
    $ele['limiting']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_LIMITING_DESC : ''));
    $ele['records'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_RECORDS : ''), 'records', 10, 10, '250');
    $ele['records']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_RECORDS_DESC : ''));
    $ele['wait'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_WAIT : ''), 'wait', 10, 10, '40');
    $ele['wait']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_IMPORT_WAIT_DESC : ''));

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = [];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_genre_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('genre', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_GENRE, 'genre', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_GENRE, 'genre', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('gid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'genre');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, $object->getVar('name'));
    $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_NAME_DESC : ''));
    $ele['albums']  = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_ALBUMS : ''), $object->getVar('albums'));
    $ele['artists'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_ARTISTS : ''), $object->getVar('artists'));
    $ele['songs']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_SONGS : ''), $object->getVar('songs'));
    $ele['hits']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_HITS : ''), $object->getVar('hits'));
    $ele['rank']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_GENRE_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_voice_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('voice', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_VOICE, 'voice', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_VOICE, 'voice', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('vcid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'voice');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, $object->getVar('name'));
    $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_NAME_DESC : ''));
    $ele['albums']  = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_ALBUMS : ''), $object->getVar('albums'));
    $ele['artists'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_ARTISTS : ''), $object->getVar('artists'));
    $ele['songs']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_SONGS : ''), $object->getVar('songs'));
    $ele['hits']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_HITS : ''), $object->getVar('hits'));
    $ele['rank']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOICE_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_albums_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('albums', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_ALBUMS, 'albums', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_ALBUMS, 'albums', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('abid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'albums');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['cid'] = new SonglistFormSelectCategory((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_CATEGORY : ''), $id . '[cid]', $object->getVar('cid'), 1, false, false, false);
    $ele['cid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_CATEGORY_DESC : ''));
    $ele['title'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_TITLE : ''), $id . '[title]', (false === $as_array ? 55 : 21), 128, $object->getVar('title'));
    $ele['title']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_TITLE_DESC : ''));
    $ele['image'] = new \XoopsFormFile((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_UPLOAD_POSTER : ''), 'image', $GLOBALS['songlistModuleConfig']['filesize_upload']);
    $ele['image']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_UPLOAD_POSTER_DESC : ''));
    if (strlen($object->getVar('image')) > 0 && file_exists($GLOBALS['xoops']->path($object->getVar('path') . $object->getVar('image')))) {
        $ele['image_preview'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_POSTER : ''), '<img src="' . $object->getImage('image') . '" width="340px">');
        $ele['image_preview']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_POSTER_DESC : ''));
    }
    $ele['artists'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_ARTISTS : ''), $object->getVar('artists'));
    $ele['songs']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_SONGS : ''), $object->getVar('songs'));
    $ele['hits']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_HITS : ''), $object->getVar('hits'));
    $ele['rank']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ALBUMS_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name', 'id', 'source'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_artists_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('artists', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_ARTISTS, 'artists', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_ARTISTS, 'artists', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $id = $object->getVar('aid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'artists');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['cids'] = new SonglistFormSelectCategory((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_CATEGORY : ''), $id . '[cids]', $object->getVar('cids'), 7, true, false, false);
    $ele['cids']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_CATEGORY_DESC : ''));
    //$ele['singer'] = new SonglistFormSelectSinger(($as_array==false?_FRM_SONGLIST_FORM_ARTISTS_SINGER:''), $id.'[singer]', $object->getVar('singer'), 1, false, false, false);
    //$ele['singer']->setDescription(($as_array==false?_FRM_SONGLIST_FORM_ARTISTS_SINGER_DESC:''));
    $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, $object->getVar('name'));
    $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_NAME_DESC : ''));
    $ele['albums'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_ALBUMS : ''), $object->getVar('albums'));
    $ele['songs']  = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_SONGS : ''), $object->getVar('songs'));
    $ele['hits']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_HITS : ''), $object->getVar('hits'));
    $ele['rank']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_ARTISTS_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name', 'mimetype', 'support'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_category_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('category', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_CATEGORY, 'category', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_CATEGORY, 'category', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('cid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'category');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['pid'] = new SonglistFormSelectCategory((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_PARENT : ''), $id . '[pid]', $object->getVar('pid'), 1, false, $object->getVar('cid'));
    $ele['pid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_PARENT_DESC : ''));
    $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, $object->getVar('name'));
    $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_NAME_DESC : ''));
    $description_configs           = [];
    $description_configs['name']   = $id . '[description]';
    $description_configs['value']  = $object->getVar('description');
    $description_configs['rows']   = 35;
    $description_configs['cols']   = 60;
    $description_configs['width']  = '100%';
    $description_configs['height'] = '400px';
    $ele['description']            = new \XoopsFormEditor(_FRM_SONGLIST_FORM_CATEGORY_DESCRIPTION, $GLOBALS['songlistModuleConfig']['editor'], $description_configs);
    $ele['description']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_DESCRIPTION_DESC : ''));
    $ele['image'] = new \XoopsFormFile((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_UPLOAD_POSTER : ''), 'image', $GLOBALS['songlistModuleConfig']['filesize_upload']);
    $ele['image']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_UPLOAD_POSTER_DESC : ''));
    if (strlen($object->getVar('image')) > 0 && file_exists($GLOBALS['xoops']->path($object->getVar('path') . $object->getVar('image')))) {
        $ele['image_preview'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_POSTER : ''), '<img src="' . $object->getImage('image') . '" width="340px">');
        $ele['image_preview']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_POSTER_DESC : ''));
    }
    $ele['artists'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_ARTISTS : ''), $object->getVar('artists'));
    $ele['songs']   = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_SONGS : ''), $object->getVar('songs'));
    $ele['hits']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_HITS : ''), $object->getVar('hits'));
    $ele['rank']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_CATEGORY_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name', 'id', 'source'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_utf8map_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('utf8map', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_UTF8MAP, 'utf8map', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_UTF8MAP, 'utf8map', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('utfid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'utf8map');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['from'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_FROM : ''), $id . '[from]', (false === $as_array ? 6 : 4), 2, $object->getVar('from'));
    $ele['from']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_FROM_DESC : ''));
    $ele['to'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_TO : ''), $id . '[to]', (false === $as_array ? 6 : 4), 2, $object->getVar('to'));
    $ele['to']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_TO_DESC : ''));

    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_UTF8MAP_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['from', 'to'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_requests_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('requests', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_REQUESTS, 'requests', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_REQUESTS, 'requests', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('rid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'requests');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['artist'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_ARTIST : ''), $id . '[artist]', (false === $as_array ? 55 : 21), 128, $object->getVar('artist'));
    $ele['artist']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_ARTIST_DESC : ''));
    $ele['album'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_ALBUM : ''), $id . '[album]', (false === $as_array ? 55 : 21), 128, $object->getVar('album'));
    $ele['album']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_ALBUM_DESC : ''));
    $ele['title'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_TITLE : ''), $id . '[title]', (false === $as_array ? 55 : 21), 128, $object->getVar('title'));
    $ele['title']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_TITLE_DESC : ''));
    $ele['lyrics'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_LYRICS : ''), $id . '[lyrics]', (false === $as_array ? 55 : 21), 128, $object->getVar('lyrics'));
    $ele['lyrics']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_LYRICS_DESC : ''));

    if (is_object($GLOBALS['xoopsUser'])) {
        $ele['uid']  = new \XoopsFormHidden('uid', $GLOBALS['xoopsUser']->getVar('uid'));
        $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, ($object->isNew() ? $GLOBALS['xoopsUser']->getVar('name') : $object->getVar('name')));
        $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_NAME_DESC : ''));
        $ele['email'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_EMAIL : ''), $id . '[email]', (false === $as_array ? 55 : 21), 128, ($object->isNew() ? $GLOBALS['xoopsUser']->getVar('email') : $object->getVar('email')));
        $ele['email']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_EMAIL_DESC : ''));
    } else {
        $ele['uid']  = new \XoopsFormHidden('uid', 0);
        $ele['name'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_NAME : ''), $id . '[name]', (false === $as_array ? 55 : 21), 128, ($object->isNew() ? '' : $object->getVar('name')));
        $ele['name']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_NAME_DESC : ''));
        $ele['email'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_EMAIL : ''), $id . '[email]', (false === $as_array ? 55 : 21), 128, ($object->isNew() ? '' : $object->getVar('email')));
        $ele['email']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_EMAIL_DESC : ''));
    }
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_REQUESTS_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = ['name', 'email'];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_songs_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('songs', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_SONGS, 'songs', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_SONGS, 'songs', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('sid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'songs');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $ele['cid'] = new SonglistFormSelectCategory((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_CATEGORY : ''), $id . '[cid]', (isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $object->getVar('cid')), 1, false);
    $ele['cid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_CATEGORY_DESC : ''));
    if ($GLOBALS['songlistModuleConfig']['genre']) {
        $ele['gids'] = new SonglistFormSelectGenre((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_GENRE : ''), $id . '[gids]', (isset($_REQUEST['gids']) ? $_REQUEST['gids'] : $object->getVar('gids')), 8, true);
        $ele['gids']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_GENRE_DESC : ''));
    }
    if ($GLOBALS['songlistModuleConfig']['voice']) {
        $ele['vcid'] = new SonglistFormSelectVoice((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_VOICE : ''), $id . '[vcid]', (isset($_REQUEST['vcid']) ? $_REQUEST['vcid'] : $object->getVar('vcid')), 1, false);
        $ele['vcid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_VOICE_DESC : ''));
    }
    if ($GLOBALS['songlistModuleConfig']['album']) {
        $ele['abid'] = new SonglistFormSelectAlbum((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_ALBUM : ''), $id . '[abid]', $object->getVar('abid'), 1, false);
        $ele['abid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_ALBUM_DESC : ''));
    }
    $ele['aids'] = new SonglistFormSelectArtist((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_ARTISTS : ''), $id . '[aids]', $object->getVar('aids'), 7, true);
    $ele['aids']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_ARTISTS_DESC : ''));
    $ele['songid'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_SONGID : ''), $id . '[songid]', (false === $as_array ? 25 : 15), 32, $object->getVar('songid'));
    $ele['songid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_SONGID_DESC : ''));
    $ele['traxid'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_TRAXID : ''), $id . '[traxid]', (false === $as_array ? 25 : 15), 32, $object->getVar('traxid'));
    $ele['traxid']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_TRAXID_DESC : ''));
    $ele['title'] = new \XoopsFormText((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_TITLE : ''), $id . '[title]', (false === $as_array ? 55 : 21), 128, $object->getVar('title'));
    $ele['title']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_TITLE_DESC : ''));
    $description_configs           = [];
    $description_configs['name']   = $id . '[lyrics]';
    $description_configs['value']  = $object->getVar('lyrics');
    $description_configs['rows']   = 35;
    $description_configs['cols']   = 60;
    $description_configs['width']  = '100%';
    $description_configs['height'] = '400px';
    $ele['lyrics']                 = new \XoopsFormEditor(_FRM_SONGLIST_FORM_SONGS_LYRICS, $GLOBALS['songlistModuleConfig']['editor'], $description_configs);
    $ele['lyrics']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_LYRICS_DESC : ''));
    $ele['mp3'] = new \XoopsFormFile((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_MP3 : ''), 'mp3' . $id, $GLOBALS['songlistModuleConfig']['mp3_filesize']);
    $ele['mp3']->setDescription((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_MP3_DESC : ''));
    $categoryHandler = xoops_getModuleHandler('category', 'songlist');
    $criteria        = new \CriteriaCompo(new \Criteria('cid', (!empty($_REQUEST['cid'])) ? (int)$_REQUEST['cid'] : $object->getVar('cid')));
    $all_categories  = $categoryHandler->getObjects($criteria, true, false);

    // Dynamic fields
    $extrasHandler = xoops_getModuleHandler('extras');
    $gpermHandler  = xoops_getHandler('groupperm');
    $moduleHandler = xoops_getHandler('module');
    $xoModule      = $moduleHandler->getByDirname('songlist');
    $modid         = $xoModule->getVar('mid');

    if (is_object($GLOBALS['xoopsUser'])) {
        $groups = $GLOBALS['xoopsUser']->getGroups();
    } else {
        $groups = [XOOPS_GROUP_ANONYMOUS => XOOPS_GROUP_ANONYMOUS];
    }

    $count_fields = 0;
    $fields       = $extrasHandler->loadFields();

    $required = [];
    $elements = [];
    $weights  = [];
    if ($object->getVar('sid') > 0) {
        $extra = $extrasHandler->get($object->getVar('sid'));
    } else {
        $extra = $extrasHandler->create();
    }
    $allnames = [];
    if (is_array($fields)) {
        foreach (array_keys($fields) as $i) {
            if ((0 <> $object->getVar('sid') && $gpermHandler->checkRight('songlist_edit', $fields[$i]->getVar('field_id'), $groups, $modid))
                || (0 == $object->getVar('sid') && $gpermHandler->checkRight('songlist_post', $fields[$i]->getVar('field_id'), $groups, $modid))) {
                $fieldinfo['element']  = $fields[$i]->getEditElement($object, $extra);
                $fieldinfo['required'] = $fields[$i]->getVar('field_required');
                foreach ($fields[$i]->getVar('cids') as $catidid => $cid) {
                    if (!in_array($fields[$i]->getVar('field_name'), $allnames)) {
                        $allnames[] = $fields[$i]->getVar('field_name');
                        if (in_array($cid, array_keys($all_categories)) || $cid == ((!empty($_REQUEST['cid'])) ? (int)$_REQUEST['cid'] : $object->getVar('cid'))) {
                            $key              = $all_categories[$cid]['weight'] * $count_fields + $object->getVar('cid');
                            $elements[$key][] = $fieldinfo;
                            $weights[$key][]  = $fields[$i]->getVar('field_weight');
                        } elseif (in_array(0, $fields[$i]->getVar('cids'))) {
                            $key              = $all_categories[$cid]['weight'] * $count_fields + $object->getVar('cid');
                            $elements[$key][] = $fieldinfo;
                            $weights[$key][]  = $fields[$i]->getVar('field_weight');
                        }
                    }
                }
            }
        }
    }
    if (is_array($elements)) {
        ksort($elements);
        foreach (array_keys($elements) as $k) {
            array_multisort($weights[$k], SORT_ASC, array_keys($elements[$k]), SORT_ASC, $elements[$k]);
            foreach (array_keys($elements[$k]) as $i) {
                $ele[$k] = $elements[$k][$i]['element'];
                if (true === $elements[$k][$i]['required']) {
                    $required[$k] = $elements[$k][$i]['element']->getName();
                }
            }
        }
    }

    if (!class_exists('TagFormTag')) {
        $ele['tags'] = new \XoopsFormHidden('tags', $object->getVar('tags'));
    } else {
        $ele['tags'] = new TagFormTag('tags', 60, 255, $object->getVar('sid'), $object->getVar('cid'));
    }

    $ele['hits'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_HITS : ''), $object->getVar('hits'));
    $ele['rank'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_RANK : ''), number_format(($object->getVar('rank') > 0 && $object->getVar('votes') > 0 ? $object->getVar('rank') / $object->getVar('votes') : 0), 2) . ' of 10');
    if ($object->getVar('created') > 0) {
        $ele['created'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_CREATED : ''), date(_DATESTRING, $object->getVar('created')));
    }
    if ($object->getVar('updated') > 0) {
        $ele['updated'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_SONGS_UPDATED : ''), date(_DATESTRING, $object->getVar('updated')));
    }
    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}

function songlist_votes_get_form($object, $as_array = false)
{
    if (!is_object($object)) {
        $handler = xoops_getModuleHandler('votes', 'songlist');
        $object  = $handler->create();
    }

    xoops_loadLanguage('forms', 'songlist');
    $ele = [];

    if ($object->isNew()) {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_ISNEW_CART, 'votes', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'new');
    } else {
        $sform       = new \XoopsThemeForm(_FRM_SONGLIST_FORM_EDIT_CART, 'votes', $_SERVER['PHP_SELF'], 'post', true);
        $ele['mode'] = new \XoopsFormHidden('mode', 'edit');
    }

    $sform->setExtra("enctype='multipart/form-data'");

    $id = $object->getVar('cid');
    if (empty($id)) {
        $id = '0';
    }

    $ele['op']  = new \XoopsFormHidden('op', 'votes');
    $ele['fct'] = new \XoopsFormHidden('fct', 'save');
    if (false === $as_array) {
        $ele['id'] = new \XoopsFormHidden('id', $id);
    } else {
        $ele['id'] = new \XoopsFormHidden('id[' . $id . ']', $id);
    }
    $ele['sort']   = new \XoopsFormHidden('sort', isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'created');
    $ele['order']  = new \XoopsFormHidden('order', isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC');
    $ele['start']  = new \XoopsFormHidden('start', isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0);
    $ele['limit']  = new \XoopsFormHidden('limit', isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0);
    $ele['filter'] = new \XoopsFormHidden('filter', isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '1,1');

    $songsHandler = xoops_getModuleHandler('songs', 'songlist');
    $userHandler  = xoops_getHandler('user');
    $song         = $songsHandler->get($object->getVar('sid'));
    $user         = $userHandler->get($object->getVar('uid'));
    if (is_object($song)) {
        $ele['sid'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_SONG : ''), $song->getVar('title'));
    } else {
        $ele['sid'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_SONG : ''), $object->getVar('sid'));
    }
    if (is_object($user)) {
        $ele['uid'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_USER : ''), $user->getVar('uname'));
    } else {
        $ele['uid'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_USER : ''), _GUESTS);
    }
    $ele['ip']      = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_IP : ''), $object->getVar('ip'));
    $ele['netaddy'] = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_NETADDY : ''), $object->getVar('netaddy'));
    $ele['rank']    = new \XoopsFormLabel((false === $as_array ? _FRM_SONGLIST_FORM_VOTES_RANK : ''), $object->getVar('rank') . ' of 10');

    if (true === $as_array) {
        return $ele;
    }

    $ele['submit'] = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');

    $required = [];

    foreach ($ele as $id => $obj) {
        if (in_array($id, $required)) {
            $sform->addElement($ele[$id], true);
        } else {
            $sform->addElement($ele[$id], false);
        }
    }

    return $sform->render();
}
