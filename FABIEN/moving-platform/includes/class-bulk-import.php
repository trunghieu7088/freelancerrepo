<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Import_Feature
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();
	}

    function init_hook()
    {
        add_action('admin_menu',array($this, 'add_bulk_import_admin_menu' ),99);
        add_action('admin_action_upload_excel_bulk_import',array($this,'upload_excel_bulk_import_action'));        
        add_action('admin_action_import_excel_file_by_url',array($this,'import_excel_file_by_url_action'));        
        add_action('run_imported_excel_files',array($this,'run_imported_excel_files_action'));
	    add_filter('cron_schedules',array($this,'processing_excel_file_schedules') , 999);
        add_action('wp_ajax_delete_import_excel_file',array($this,'delete_import_excel_file_action'));
        add_action('admin_head',array($this,'set_up_info_js_admin'),1);
	}
    
    
    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    

    function set_up_info_js_admin()
    {
        $admin_ajax_url=admin_url('admin-ajax.php');
        ?>
        <script type="text/javascript">
            let admin_ajax_url='<?php echo $admin_ajax_url; ?>'; 
        </script>
        <?php
    }

    function add_bulk_import_admin_menu()
    {
        add_menu_page( 'Data Bulk Import', 'Bulk Import', 'manage_options', 'bulk-import-custom', array($this,'bulk_import_function_init'),'dashicons-database-import',8 );

         add_submenu_page(
            'bulk-import-custom',        // Parent slug (same as the slug in add_menu_page)
            'Process Data',        // Page title
            'Process Data',              // Sub-menu title
            'manage_options',        // Capability
            'bulk-process-data',         // Sub-menu slug
            array($this,'data_process_callback'), // Function to display the sub-menu content
        );
    }

    function bulk_import_function_init()
    {
        ?>
        <h3>Please select excel file to import data</h3>
        <table>
		    <tr class="form-field">       
                <td>      
                    <form method="POST" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php' ); ?>">
                        <input type="hidden" name="action" value="upload_excel_bulk_import" />                                                
                        <input type="file" class="" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" id="imported_excel_file" name="imported_excel_file" value="Add file"/>
                        <br>
                        <br>               
                        <input type="submit" value="Submit" class="button button-primary">
                    </form>
                </td>
            </tr>
	</table>
        <?php
         if(isset($_GET['Addsuccess']))
         {
             if($_GET['Addsuccess']=='true')
             {
                  echo '<div class="notice notice-success is-dismissible">
                 <p>Import successfully</p>
             </div>';
             }
             if($_GET['Addsuccess']=='false')
             {
                 echo '<div class="notice notice-error is-dismissible">
                 <p>Import failed, there is something wrong with your file</p>
             </div>';
             }
         }           
    }

    function data_process_callback()
    {
        ?>
        <h2><?php _e('Available Files','moving_platform'); ?></h2>
        <?php
        $current_list_files=get_option('list_file_bulk_import');           
        if($current_list_files)
        {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($current_list_files as $file_item): ?>
                        <tr>
                            <td><?php echo $file_item['file_url']; ?></td>
                            <td><?php echo $file_item['status']; ?></td>
                            <td><button class="button import-remove-file" type="button" data-file-url="<?php echo $file_item['file_url']; ?>" data-attach-id="<?php echo $file_item['attach_id'] ?>">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="POST" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php' ); ?>">
                <input type="hidden" name="action" value="import_excel_file_by_url" /> 
                <br><br>                                                                                          
                <input type="submit" value="Run" class="button button-primary">
            </form>
            <?php
            if(isset($_GET['run']))
            {
                echo '<div class="notice notice-success is-dismissible">
                <p>The importing system is running !</p>
            </div>';
            }   
        }
    }

    function upload_excel_bulk_import_action()
    {
        if ( ! function_exists( 'wp_handle_upload' ) ) 
        {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if (!empty($_FILES['imported_excel_file'])) {
            $uploadedfile = $_FILES['imported_excel_file'];
        }
                
        $upload_dir = wp_upload_dir();
        $upload_overrides = array('test_form' => false);

	    $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        

        if ( $movefile && ! isset( $movefile['error'] ) ) 
        {              
            $attachment = array(       
                'guid'  => $upload_dir['url'].'/'.sanitize_file_name($uploadedfile['name']), 
                'post_mime_type' => $uploadedfile['type'],
                'post_title'     =>  sanitize_file_name($uploadedfile['name']),
                'post_content'   => 'imported excel file',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $uploaded_file['name']);    
            if($attach_id)
            {
                $current_list_files=get_option('list_file_bulk_import');           
                if(!$current_list_files)
                {
                    $current_list_files=array();
                }
                $current_list_files[]=array('attach_id'=>$attach_id,'file_url'=>$movefile['url'],'status'=>'none');
                update_option('list_file_bulk_import',$current_list_files);    
            }
                            
            wp_redirect( $_SERVER['HTTP_REFERER'].'&Addsuccess=true' );
        } 
        else 
        {   
           wp_redirect( $_SERVER['HTTP_REFERER'].'&Addsuccess=false' );
        }
      
        exit();
    }

    function import_excel_file_by_url_action()
    {
        /*if (!wp_next_scheduled('run_imported_excel_files')) {
            wp_schedule_event(time(), 'daily_midnight', 'run_imported_excel_files');           
        }*/
        wp_schedule_event(time(), 'daily_midnight', 'run_imported_excel_files'); 
        wp_redirect( $_SERVER['HTTP_REFERER'].'&run=true' );
        exit(); 
    }

    function processing_excel_file_schedules()
    {
          // Add a new custom cron schedule named 'daily'
        $schedules['daily_midnight'] = array(
                'interval' => 24 * 60 * 60, // 24 hours in seconds
                //'interval' => 100,
                'display' => 'Once Daily at 12 PM',
        );
        return $schedules;
    }

    //delete excel file from server and remove from the list
    function delete_import_excel_file_action()
    {
        //if not admin , stop !!
        if(!current_user_can('manage_options'))
        {
            die('');
        } 

        $result_delete=wp_delete_attachment((int)$_POST['delete_file_id']);
        //$result_delete=wp_delete_attachment((int)$_POST['delete_file_id'],true);
        if($result_delete)
        {
            $current_list_files=get_option('list_file_bulk_import');  
            foreach($current_list_files  as $key => $file_item)
            {
                if ($file_item['attach_id'] ==$_POST['delete_file_id']) {
                    // Remove the item from the array
                    unset($current_list_files[$key]);
                    break; // Exit the loop once the item is found and removed
                }
            }
            //re-update after delete file
            update_option('list_file_bulk_import',$current_list_files);
            $response = array(
                'success' => true,
                'message' => __('File has been deleted.','moving_platform'),  

            );

        }
        else
        {
            $response = array(
                'success' => false,
                'message' => __('Failed to delete file.','moving_platform'),           
            );
        }

        wp_send_json($response);
        die();
    }
    function run_imported_excel_files_action()
    {
          require ('phpexcel/vendor/autoload.php');
          $spreadsheet = new Spreadsheet();
  
          $inputFileType = 'Xlsx';
  
          //if no files, stop !!
          $current_list_files=get_option('list_file_bulk_import');  
          if(!$current_list_files)
          {
              die('');
          }            
          foreach($current_list_files as $key_file => $file_import)
          {
               
              //update status of file item in option array
              $current_list_files[$key_file]['status']='running';
              update_option('list_file_bulk_import',$current_list_files);

              $filename = $file_import['file_url'];
              $file = file_get_contents($filename);        
              $inputFileName = 'dataimport.xlsx';
              file_put_contents($inputFileName, $file);
      
              $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
              $reader->setReadDataOnly(true);
              $spreadsheet = $reader->load($inputFileName);
      
              $worksheet = $spreadsheet->getActiveSheet();
      
              $worksheetItems=$worksheet->toArray();
      
              $newsheet=array_slice($worksheetItems,1);
      
              $labelArray=array('name','code','address','postal');
              
              foreach($newsheet as $key => $value)
              {                                 
                  $value=array_filter($value);            
                  if(is_array($value) && count($value)==4)
                  {
                      $newValue=array_combine($labelArray,$value);
                      $added_term=wp_insert_term(ucwords($newValue['name']),'city');   
                      if($added_term)
                      {
                          update_term_meta($added_term['term_id'],'code_commune',$newValue['code']);
                          update_term_meta($added_term['term_id'],'detail_address',$newValue['address']);
                          update_term_meta($added_term['term_id'],'postal_code',$newValue['postal']);
                      }  
                  }
              }
              
               //update status of file item in option array after done
              $current_list_files[$key_file]['status']='completed';
              update_option('list_file_bulk_import',$current_list_files);
          }
       
    }
}

new Import_Feature();