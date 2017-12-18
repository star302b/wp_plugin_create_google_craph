<style>
    .switcher {
        width: 124px;
        height: 49px;
        cursor: pointer;
        position: relative;
        float: left;
    }

    .switcher * {
        transition: all .2s;
        -moz-transition: all .2s;
        -webkit-transition: all .2s;
        -o-transition: all .2s;
        -ms-transition: all .2s;
    }

    .switcher .sw_btn {
        background: url('<?php echo WP_CREATE_GRAPH_URL;?>assets/img/btn.png') 0% 0% no-repeat;
        width: 49px;
        height: 49px;
        display: block;
        cursor: pointer;
        z-index: 1;
        position: relative;
    }

    .switcher .bg { background: url('<?php echo WP_CREATE_GRAPH_URL;?>assets/img/bg-off.png') 0% 0% no-repeat; }
    .switcher input.switcher-value:checked ~ .sw_btn { margin-left: 68px; }
    .switcher input.switcher-value:checked ~ .sw_btn ~ .bg { background: url('<?php echo WP_CREATE_GRAPH_URL;?>assets/img/bg-on.png') 0% 0% no-repeat; }
    .switcher input.switcher-value { display: none; }
    .switcher .bg {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
    }
    .remote{
        float: left;
        width: 100%;
    }
</style>
<div>
    <h3>Connection Type</h3>
</div>
<form  action="" method="post">
<label for="" style="float: left">Local</label>
<div class="switcher">
    <input id="sw" type="checkbox" class="switcher-value"  name="connection" <?php if(get_option('wp_create_graph_connection') == "remote")echo 'value="local" checked';else{echo 'value="remote" ';} ?> >
    <label for="sw" class="sw_btn"></label>
    <div class="bg"></div>
</div>
<label for="sw">Remote</label>
<div class="remote"></div>
<input type="submit"  name="submit" value="<?php _e('Save Changes'); ?>" ></form>
<script>
    var arr = [];
    var i = 0;
    jQuery(":checkbox").change(function(){
        if(this.checked){
            arr[i] = jQuery(this).val();
            i++;
            jQuery.ajax({
                url: '<?php echo WP_CREATE_GRAPH_URL;?>includes/ajax_admin.php',
                method: 'POST',
                data: {
                    "host":'<?php echo get_option('wp_create_graph_host'); ?>',
                    "database":'<?php echo get_option('wp_create_graph_database'); ?>',
                    "user":'<?php echo get_option('wp_create_graph_user'); ?>',
                    "password":'<?php echo get_option('wp_create_graph_password'); ?>',},
                }).done(function(data){
                jQuery(".remote").html(data);
            });
        }else{
            var val = jQuery(this).val();
            var index = arr.indexOf(val);
            arr.splice(index, 1);
            i--;
            jQuery(".remote").html('');
        }
    });
</script>
<?php
if ( isset($_POST['submit']) )
{
    $host = $_POST['host'];
    $database = $_POST['Database'];
    $user = $_POST['User'];
    $password = $_POST['Password'];
    if(isset($_POST['connection']) && $_POST['connection'] !='')
        update_option('wp_create_graph_connection',$_POST['connection']);
    update_option('wp_create_graph_host', $host);
    update_option('wp_create_graph_database', $database);
    update_option('wp_create_graph_user', $user);
    update_option('wp_create_graph_password', $password);
}
?>