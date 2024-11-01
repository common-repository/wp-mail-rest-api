<div class="wrap">
    <h1><?php esc_html_e('WP MAIL RESR-API Webhooks Log', __DIR__);?></h1>
    
    <?php echo isset( $success_message_resend_webhook_request ) && $success_message_resend_webhook_request? '<p style="color:green">' . __( $success_message_resend_webhook_request, __DIR__ ) . '</p>' : ''?>
    <?php echo isset( $error_message_resend_webhook_request ) && $error_message_resend_webhook_request? '<p style="color:red">' . __( $error_message_resend_webhook_request, __DIR__ ) . '</p>' : ''?>
    
    <div style="width: 100%; max-height: 500px; overflow-y: scroll;">
    <table class="widefat fixed" cellspacing="0">
        
        <thead>
            <tr>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('CREATED', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('TO', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('SUBJECT', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('RESPONSE', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('STATUS', __DIR__);?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('CREATED', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('TO', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('SUBJECT', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('RESPONSE', __DIR__);?></th>
                <th class="manage-column column-columnname" scope="col"><?php esc_html_e('STATUS', __DIR__);?></th>
            </tr>
        </tfoot>

        <tbody>
            
            <?php if( !$log_results || !is_array($log_results) || count($log_results) == 0 ):?>
            
            <tr class="alternate">
                <td class="column-columnname" colspan="5"><?php esc_html_e('No data to show.', __DIR__);?></td>
            </tr>
               
            <?php else:?>
            
            <?php foreach($log_results as $log_row):  $args_obj = json_decode( $log_row->args_json );?>
            
                <tr class="alternate">
                    <td class="column-columnname"><?php echo date( 'm/d/Y H:i:s', strtotime( $log_row->created_at ) ); ?></td>
                    <td class="column-columnname"><?php echo isset($args_obj, $args_obj->to)? $args_obj->to: ''; ?></td>
                    <td class="column-columnname"><?php echo isset($args_obj, $args_obj->subject)? $args_obj->subject: ''; ?></td>
                    <td class="column-columnname"><?php echo substr($log_row->response, 0, 20) . '...'; ?></td>
                    <td class="column-columnname"><?php echo $log_row->status? __('Successful', __DIR__): __('Failed', __FILE__); ?>
                    <?php if( !$log_row->status ): ?>
                        <br />
                        <form method="post">
                            <?php wp_nonce_field( 'resend_webhook_request' ); ?>
                            <input type="hidden" name="webhook_log_id" value="<?php echo $log_row->id;?>" />
                            <input type="submit" value="<?php esc_html_e('Resend Request', __DIR__);?>" />
                        </form>
                        
                    <?php endif;?>
                    </td>
                </tr>
            <?php endforeach;?>
            <?php endif;?>
        </tbody>
    </table>
    </div>
</div>