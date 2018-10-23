<?php

if (isset($_POST['file'])) {
    $dataFile = $_POST['file'];
    nr_bpi_readCSV($dataFile);

} else {
    wp_die("<center><h1>Not Authorized</h1></center>");
}

$log = "";

function nr_bpi_readCSV($target)
{

    global $log;
    $log .= "{csv_result}";
    /*Faster Processing*/

    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    global $wpdb;
    $wpdb->query('SET autocommit = 0;');

    /*Defining Variables*/
    $file = $target;
    $custom_post_type = "nr_provider";
   


    /*Handle CSV File*/
    // Check if file is writable, then open it in 'read only' mode
    if (is_readable($file) && $_file = fopen($file, "r")) {


        $post = array();

        // Get first row in CSV, which is of course the headers
        $header = fgetcsv($_file);

        while ($row = fgetcsv($_file)) {
            if (array(NULL) === $row) {
                continue;
            }
            foreach ($header as $i => $key) {
                $key = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $key);
                $key = urlencode($key);
                $key = trim($key);
                $post[$key] = $row[$i];
            }

            $data[] = $post;
        }

        fclose($_file);


        /*Define Functions*/

        $optimizeProviderNumber = function ($p_number) {
            //Convert Phone Number to Number Only
            $p_phone = preg_replace("/[^0-9]/", "", $p_number);
            return $p_phone;
        };





        $providersProcessed = array();


        /*Start Entry Loop*/
        foreach ($data as $idx => $post) {

            $log .= "********************** Starting " . $post['name'] . " [$idx] ********************" . PHP_EOL;

            if (post_exists($post["name"]) != 0) {
                $log .= "[Existing Provider]" . PHP_EOL;
                //Get Current Practitioner's Post ID

                $current_provider = get_page_by_title($post["name"], 'ARRAY_A', $custom_post_type);
                if(!in_array($post['name'], $providersProcessed))
                {
                    array_push($providersProcessed, $post['name']);
                    $log .= "[Updating Provider Data]".PHP_EOL;
                    updateProviderInfo($current_provider['ID'], $post['name'], $post['address'], $post['city'], $post['state'], $post['zip'], $post['phone'], $post['show_company'], $post['company_id'], $post['program'], $post['effective_date'], $post['marketing_emails'], $post['url'], $post['latitude'], $post['longitude']);


                    $log .= "[Updating Database Data]".PHP_EOL;
                    if(intval($post['show_company']) == 1) {
                        nr_bpi_addProvidersToDB($post["company_id"], $current_provider["ID"], $post["name"], $post["address"], $post["city"], $post["state"], $post["zip"], nr_bpi_addhttp($post["url"]), preg_replace("/[^0-9]/", "", $post['phone']), $post["latitude"], $post['longitude'], $current_provider['post_name']);
                    }

                }


                //Update Practitioner
                checkPractitioners($current_provider['ID'], $post['first_name'], $post['last_name'], $post['degree'], $post['bio'], $post['doctor_email'], $optimizeProviderNumber($post['doctor_phone']), $post['show_profile'], $post['photo_id'], $post['profile_id']);


                continue;
            }

            $log .= "[New Entry]" . PHP_EOL;

            //Create Unique Slug
            $unique_slug = sanitize_title_with_dashes($post["name"]) . "-" . $post["city"] . "-" . $post["state"] . "-" . $post["zip"];
            $unique_slug = sanitize_title($unique_slug);

            // Insert New Post
            $post["id"] = wp_insert_post(array(
                "post_title" => $post["name"],
                "post_name" => $unique_slug,
                "post_content" => $post["practice_bio"],
                "ping_status" => 'closed',
                'comment_status' => 'closed',
                "post_type" => $custom_post_type,
                "post_status" => getPostStatus($post['show_company']),
            ));

            updateProviderInfo($post['id'], $post['name'], $post['address'], $post['city'], $post['state'], $post['zip'], $post['phone'], $post['show_company'], $post['company_id'], $post['program'], $post['effective_date'], $post['marketing_emails'], $post['url'], $post['latitude'], $post['longitude']);
            addNewPractitioner($post['id'], $post['first_name'], $post['last_name'], $post['degree'], $post['bio'], $post['doctor_email'], $optimizeProviderNumber($post['doctor_phone']), $post['show_profile'],  $post['photo_id'], $post['profile_id']);
            if(intval($post['show_company']) == 1)
            {
                nr_bpi_addProvidersToDB($post["company_id"], $post["id"], $post["name"], $post["address"], $post["city"], $post["state"], $post["zip"], nr_bpi_addhttp($post["url"]), preg_replace("/[^0-9]/", "", $post['provider_phone']), $post["latitude"], $post['longitude'], $unique_slug);
            }


            $log .= "******************************************" . PHP_EOL;
        }

    } else {
        $errors[] = "File '$file' could not be opened. Check the file's permissions to make sure it's readable by your server.";
        echo "File '$file' could not be opened. Check the file's permissions to make sure it's readable by your server.";
    }

    /*END Faster Processign*/
    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);
    $wpdb->query('COMMIT;');
    $wpdb->query('SET autocommit = 1;');

    $log_file_name = plugin_dir_path(__FILE__).'logs/log_' . date('d-M-Y_h_i_s') . '.log';
    file_put_contents($log_file_name, $log . "\n", FILE_APPEND);


    echo "{csv_result}<a href='".get_site_url()."/wp-content/plugins/nr-bpi/admin/partials/temp/".end(explode('/', $file ))."' target='blank'>".end(explode('/', $file ))."</a> Imported";
    echo '<br/><br/>
    <a href="'.get_site_url().'/wp-content/plugins/nr-bpi/admin/partials/logs/log_' . date('d-M-Y_h_i_s') . '.log" download ="log_'.date('d-M-Y_h_i_s').'.log" class="btn red">Download Log</a>
<hr/>';
    exit;
}


function nr_bpi_addhttp($url)
{
    if (substr( $url, 0, 4 ) === "http") {
        return $url;
    } else{
        $url = "http://" . $url;
    }
    return $url;
}

function checkPractitioners($post_id, $first_name, $last_name, $degree, $bio, $doctor_email, $doctor_phone, $show_profile, $photo_id, $profile_id) {

    /*Adds/Updates Practitioners*/
    global $log;
    $log .= "[Adding Practitioner] => " . $first_name . " " . $last_name . " -----------" . PHP_EOL;

    if (have_rows('biote_practitioner', $post_id)) {
        while (have_rows('biote_practitioner', $post_id)) {
            the_row();
            if (get_sub_field('profile_id') == $profile_id) {

                $log .= "Practitioner exists.. Updating..".PHP_EOL;
                update_sub_field('name', $first_name . " " . $last_name . ", " . $degree);
                update_sub_field('bio', $bio);
                update_sub_field('email', $doctor_email);
                update_sub_field('phone', $doctor_phone);
                update_sub_field('status', $show_profile);
                update_sub_field('image', $photo_id);
                return;

            }
        }

        addNewPractitioner($post_id, $first_name, $last_name, $degree, $bio, $doctor_email, $doctor_phone, $show_profile, $photo_id, $profile_id);
    }
}


function updateProviderInfo($post_id, $name, $address, $city, $state, $zip, $phone, $status, $company_id, $program, $effective_date, $marketing_emails, $website, $latitude, $longitude) {
    global $log;

    wp_update_post(array('ID' => $post_id, "post_status" => getPostStatus($status)));
    //Update Proiver Info
    update_field("street_address", $address, $post_id);
    update_field("city", $city, $post_id);
    update_field("state", $state, $post_id);
    update_field("zip_code", $zip, $post_id);
    //Yoast SEO Custon Fields
    $seo_tt = "_yoast_wpseo_title";
    $seo_md = "_yoast_wpseo_metadesc";
    //NR SEO TEMPLATES
    $nr_tt = $name.
        " | BioTE® Hormone Replacement Therapy";
    $nr_md = "Contact ".$name. ", your local BioTE® Medical provider in ".$city. ", ".$state. " ".$zip. ". We use bioidentical hormone replacement therapy (BHRT) to help you live healthier and happier.";
    //impliment SEO values
    update_post_meta($post_id, $seo_tt, $nr_tt);
    update_post_meta($post_id, $seo_md, $nr_md);
    //Update Phone Numbers
    clearRepeaterField($post_id, 'phone_numbers', 'phone_number');

    $provider_phones = explode(";", $phone);
    foreach($provider_phones as $provider_phone) {
        $provider_phone = preg_replace("/[^0-9]/", "", $provider_phone);
        $row = array(
            'phone_number' => $provider_phone
        );
        add_row('phone_numbers', $row, $post_id);
    }

    //END
    update_field("status", $status, $post_id);
    update_field("company_id", $company_id, $post_id);
    update_field("program", $program, $post_id);
    update_field("effective_date", wp_unslash($effective_date), $post_id);

    //Add Marketing Emails
    clearRepeaterField($post_id, 'marketing_emails', 'email');

    $mktemails = explode(";", $marketing_emails);
    foreach($mktemails as $email) {
        $row = array(
            'email' => $email
        );
        add_row('marketing_emails', $row, $post_id);
    }
    //END
    //Add WEBSITE URL
    update_field("website", nr_bpi_addhttp($website), $post_id);
    //END
    update_field("geolocation_latitude", $latitude, $post_id);
    update_field("geolocation_longitude", $longitude, $post_id);

}

function getPostStatus($provider_status){
    $provider_status = intval($provider_status);
    if($provider_status != 1){
        return 'draft';
    }
    return 'publish';
}

function addNewPractitioner($post_id, $first_name, $last_name, $degree, $bio, $doctor_email, $doctor_phone, $show_profile, $photo_id, $profile_id)
{
    global $log;
    /*Add New Practitioner if doesn't exists*/
    $log .= "[Adding New Practitioner] $first_name $last_name".PHP_EOL;
    $row = array(
        'name' => $first_name . " " . $last_name . ", " . $degree,
        'bio' => $bio,
        'email' => $doctor_email,
        'phone' => $doctor_phone,
        'status' => $show_profile,
        'image' => $photo_id,
        'profile_id' => $profile_id
    );
    add_row('biote_practitioner', $row, $post_id);
}


function clearRepeaterField($post_id, $repeatField_name, $subField_name){
    delete_field($repeatField_name, $post_id);
}

function nr_bpi_addProvidersToDB($company_id, $post_id, $name, $street, $city, $state, $zip, $website, $phone, $latitude, $longitude, $slug)
{
    global $log;
    global $wpdb;

    $table_name = $wpdb->prefix . 'nr_bpi_providers';
    $log .= "[Updating MAPS DATA]";
    $sql = "INSERT INTO $table_name (company_id, post_id, name, street, city, state, zip, website, phone, latitude, longitude, slug) VALUES (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE post_id = \"$post_id\" , name = \"$name\" , street = \"$street\" , city = \"$city\" , state = \"$state\" , zip = \"$zip\" , website = \"$website\" , phone = \"$phone\" , latitude = \"$latitude\" , longitude = \"$longitude\" , slug = \"$slug\" ";
    $sql = $wpdb->prepare($sql, $company_id, $post_id, $name, $street, $city, $state, $zip, $website, $phone, $latitude, $longitude, $slug);
    $wpdb->query($sql);


}