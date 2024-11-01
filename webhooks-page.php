<div class="wrap">
    <h1><?php esc_html_e('WP MAIL RESR-API Target Webhook URLs', __DIR__);?></h1>

    
    <?php esc_html_e('Please enter the new webhook URL', __DIR__);?>

    <form method="post">

        <?php wp_nonce_field( 'add_new_webhook_url' ); ?>
        <?php echo isset($success_message_addition) && $success_message_addition ? '<p style="color:green">' . __($success_message_addition, __DIR__ ) . '</p>' : ''; ?>
        <?php echo isset($error_message_addition) && $error_message_addition ? '<p style="color:red">' . __($error_message_addition, __DIR__ ) . '</p>' : ''; ?>

        <table class="form-table">
            <tr>
                <td><?php esc_html_e('URL', __DIR__);?></td>
                <td> <input type="text" name="new_webhook_url" value="" /> </td>
            </tr>
        </table>


        <?php
        submit_button(__('Add New URL', __DIR__ ));
        ?>
    </form>

    <h1><?php esc_html_e('URLs List', __DIR__);?></h1>

    <?php echo isset($success_message_deletion) && $success_message_deletion ? '<p style="color:green">' . __($success_message_deletion, __DIR__ ) . '</p>' : ''; ?>
    <?php echo isset($error_message_deletion) && $error_message_deletion ? '<p style="color:red">' . __($error_message_deletion, __DIR__ ) . '</p>' : ''; ?>
    
    <?php echo isset($success_message_testing_webhook) && $success_message_testing_webhook ? '<p style="color:green">' . __($success_message_testing_webhook, __DIR__ ) . '</p>' : ''; ?>
    <?php echo isset($error_message_testing_webhook) && $error_message_testing_webhook ? '<p style="color:red">' . __($error_message_testing_webhook, __DIR__ ) . '</p>' : ''; ?>


        <table class="widefat fixed" cellspacing="0">

            <thead>
                <tr>
                    <th class="manage-column column-columnname" scope="col"><?php esc_html_e('#', __DIR__);?></th>
                    <th class="manage-column column-columnname" scope="col"><?php esc_html_e('URL', __DIR__);?></th>
                    <th class="manage-column column-columnname" scope="col">&nbsp;</th>
                    <th class="manage-column column-columnname" scope="col">&nbsp;</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class="manage-column column-columnname" scope="col"><?php esc_html_e('#', __DIR__);?></th>
                    <th class="manage-column column-columnname" scope="col"><?php esc_html_e('URL', __DIR__);?></th>
                    <th class="manage-column column-columnname" scope="col">&nbsp;</th>
                    <th class="manage-column column-columnname" scope="col">&nbsp;</th>
                </tr>        
            </tfoot>


            <tbody>
                <?php 
                
                if ( $urls_to_output && count($urls_to_output) >= 0):
                
                    $counter = 1;
                
                    foreach ($urls_to_output as $key => $url): ?>
                        <tr class="alternate">
                            <td class="column-columnname"><?php echo $counter++; ?></td>
                            <td class="column-columnname"><?php echo $url; ?></td>
                            <td class="column-columnname">
                                <form method="post">
                                    <?php wp_nonce_field( 'delete_webhook_url' ); ?>
                                    <input type="hidden" name="delete_webhook_url_id" value="<?php echo $key; ?>" />
                                    <input type="submit" value="Delete" onclick="return confirm('<?php esc_html_e('Do you really want to delete this URL?', __DIR__);?>')" />
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <?php wp_nonce_field( 'test_webhook_request' ); ?>
                                    <input type="hidden" name="test_webhook_url_id" value="<?php echo $key; ?>" />
                                    <input type="submit" value="<?php esc_html_e('Test Webhook', __DIR__);?>" />
                                </form>
                            </td>
                        </tr>
                    <?php 
                    endforeach; 

                else:
                    ?>
                    <tr class="alternate">
                        <td colspan="3" class="column-columnname"><?php esc_html_e('No URLs to show.', __DIR__);?></td>
                    </tr>
                    
                    <?php
                endif;
                ?>
            </tbody>
        </table>
    
    <h1><?php esc_html_e('SMTP Settings', __DIR__);?></h1>

    
    <?php esc_html_e('Please check this box to stop wp_mail from sending any emails', __DIR__);?>

    <form method="post">

        <?php wp_nonce_field( 'disable_stmp' ); ?>
        
        <?php echo isset($success_message_disable_smtp) && $success_message_disable_smtp ? '<p style="color:green">' . __($success_message_disable_smtp, __DIR__ ) . '</p>' : ''; ?>
        <?php echo isset($error_message_disable_smtp) && $error_message_disable_smtp ? '<p style="color:red">' . __($error_message_disable_smtp, __DIR__ ) . '</p>' : ''; ?>

        <table class="form-table">
            <tr>
                
                <th scope="row"><?php esc_html_e('Disable SMTP', __DIR__);?></th>
                <td><input type="checkbox" name="disable_smtp_value" <?php echo $disable_smtp_option == 1? 'checked="checked"':'';?> /></td>
            </tr>
        </table>


        <?php
        submit_button(__('Save Settings', __DIR__ ));
        ?>
    </form>
</div>