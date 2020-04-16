<?php
/**
 * Simple ajax onepage WEBP image compressor
 *
 * Author: Tomek -=[Tox]=- Golkowski
 * Page: http://tox.ovh, http://ac.tox.ovh
 * github: https://github.com/Toxdg/, https://github.com/Toxdg/ac_web_TOOLS
 *
 */
class ac_image_webp {
    // vars
    private $source = null;
    private $root = null;
    private $wrongFiles = null; //not used - yet
    public $quality = 80;
    
    // construct
    function __construct() {
        $this->root = getcwd();
    }
    
    // get image resoure by image extension
    protected function getImageResource () {
        // get image resource
        $extension = strtolower( strrchr ( $this->source, '.' ) );
        switch ( $extension ) {
            case '.jpg':
            case '.jpeg':
                $img = @imagecreatefromjpeg( $this->source );
            break;
            case '.gif':
                $img = @imagecreatefromgif( $this->source );
            break;
            case '.png':
                $img = @imagecreatefrompng( $this->source );
            break;
            default:
                $img = false;
          break;
        }
        return $img;
    }
    // set quality
    public function setquality($quality){
        $this->quality = $source;
    }
    
    // convert file
    public function convert ( $source, $destination) {
        $quality = $this->quality;
        $this->source = $source;
        imagewebp( $this->getImageResource(), $destination, $quality );
    }
    
    // generate main list files
    public function do_list($list = ''){
        $filelist = glob("**", GLOB_ONLYDIR);
        $html = '';
        $ind = 0;
        $listarray = [];
        if($list != ''){
            $listarray = explode(",", $list);
        }
        $this->console_log('do_list get: '.$list);

        foreach ($filelist as $value) {
            if($ind < 1){
                if($list != ''){
                    if(!in_array($value, $listarray)){
                        $html .= $this->addtoresoult($value);
                    }
                }else{
                    $html .= $this->addtoresoult($value);  
                }
                $ind++;
            }  
        }
        if($html == ''){
            $html = '404_end';
        }
        return $html;
    }
    
    // return position tu main list
    private function addtoresoult($value = ''){
        $html = '';
        $files = $this->listFullFolderFiles($value);
        $this->console_log('addtoresoult get: '.$value);
        
        unset($files[array_search('.', $files, true)]);
        unset($files[array_search('..', $files, true)]);
        $this->console_log($files);
        foreach ($files as $file) {
            if($this->is_image($file) && !in_array($file, $this->$wrongFiles)){
                $ImageChange = $this->makeWebp($file);
                if (exif_imagetype($file) != IMAGETYPE_WEBP && $ImageChange === false) { 
                    $ImageChange = false;
                }else{
                    $ImageChange = true;
                }
                $html .= '<div class="row">';
                $html .= '<div class="col-sm border name file" data-files="1" data-name="'.$value.'">File: <b style="color:blue;">'.$file.'</b></div>';
                $html .= '<div class="col-sm border size"">WEBP: <b>'.$this->isWEBP($file).'</b></div>';
                if($ImageChange){
                    $html .= '<div class="col-sm border size"">Conversion: <b style="color:green;">OK</b></div>';
                }else{
                    $html .= '<div class="col-sm border size"">Conversion <b style="color:red;">FAIL</b></div>';
                }
                $html .= '</div>';
            }
        }
        return $html;
    } 
    // full list files in folders and subfolders
    private function listFullFolderFiles($dir){
        $files = scandir($dir);
        $tmpArray = array();
        unset($files[array_search('.', $files, true)]);
        unset($files[array_search('..', $files, true)]);

        // prevent empty ordered elements
        if (count($files) < 1){
            return;
        }

        foreach($files as $file){    
            if(is_dir($dir.'/'.$file)){
                $returnArray = $this->listFullFolderFiles($dir.'/'.$file);
                foreach ($returnArray as $value) {
                    $tmpArray[] = $value; 
                }
            }else{
                $tmpArray[] = $dir.'/'.$file; 
            }
        }
        return $tmpArray;
    }
    
    // image is webp
    private function isWEBP($image){
        if (exif_imagetype($image) == IMAGETYPE_WEBP) {
            return ' is WEBP';
        }else{
            return ' is not WEBP';
        }
    }
    
    // path is image 
    private function is_image($path){
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg'){
            return true;
        }
        return false;
    }

    // swap source to webp image
    private function makeWebp($file){
        $tmp = getcwd().'/web_tmp.tmp';
        $inputFile = getcwd().'/'.$file;
        @unlink($tmp); 
        $this->convert( $inputFile, $tmp);
        rename($tmp, $inputFile); 
    }
    
    // helper
    public function console_log($output, $with_script_tags = true) {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }
}

if($_POST){
    $akcja = $_POST['action_name']; 
    if($akcja == 'more'){
        echo returnajax($_POST['list']);
    }
}else{
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>webp</title>
  <meta name="description" content="webp">
  <meta name="author" content="Tomek -=[tox]=-">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
  <script type="text/javascript">
        var lock = 0;
        var list_files = '';
        // init document ready
        $(document).ready(function () {
            <?php
                if(isset($_GET['run'])){
                    if($_GET['run'] == 1){
                        ?>ac_ajax(list_file());<?php
                    }
                }
            ?>     
            calculate();
        });
		// on click more button
        $(document).on("click","#load",function(event) {
            event.preventDefault();
            if(lock == 0){
                ac_ajax(list_file());
            }
        });
        
        // calculate files
        function calculate(){
            var files = 0;
            var size = 0;
            $('#results > div.row > div.file').each(function(){
                files = files + $(this).data('files');
            });
            $('#files > span').html(files);
        }
		
        // converted dir
        function list_file(){
            lock = 1;
            $('#info').html('Loading...');
            list_files = '';
            var tmp = '';
            $('#results > div.row > div.name').each(function(){
                if(tmp != $(this).data('name')){
                    tmp = $(this).data('name');
                    list_files = list_files+$(this).data('name')+',';
                }
            });
            console.log(list_files);
            return list_files;
        }
        
		// ajax
        function ac_ajax(files){ 
            $.ajax({
                type: "POST",
                url: 'image_webp.php',
                data: {
                    list: files,
                    action_name: 'more',
                    quantity: 1
                },
                success: function(msg){
                    console.log(msg);              
                    lock = 0;
                    if(!msg.includes('404_end')){ 
                        $("#results").append(msg).delay(1000);
                        $('#info').html('Ready!');
						calculate();			
                        // if $_get run = 1 auto init
                        <?php
                            if(isset($_GET['run'])){
                                if($_GET['run'] == 1){
                                    ?>
									setTimeout(function(){
											ac_ajax(list_file());
									}, 2000);
									<?php
                                }
                            }
                        ?>
                    }else{
                        $('#info').html('All dir are listed!');
                        alert('All dir are listed!');
                        $('#load').remove();
                    }
                },
                error: function(msg) {
                    console.log('error'); 
                    lock = 0;
                    $('#info').html('ERROR!');
                }
            });
        } 
  </script>
</head>

<body>
    <div class="container" style="padding-top:40px;">
        <div class="alert alert-info" id="info">
            Ready!
        </div>
    </div>
    <div class="container" style="padding-top:40px;">
        <div id="load" class="btn btn-primary">More</div>
    </div>
    <div class="container" style="padding-top:40px;">
        <span id="files"><b>Files:</b> <span>0</span></span> <b>root path:</b> <?php echo getcwd();?>
    </div>
    <div style="padding-top:40px;"></div>
    <div class="container border" id="results">
        <?php 
            $ImageList = new ac_image_webp;
            //echo $ImageList->do_list();
        ?>
    </div>
	<div style="padding-top:40px;"></div>


</body>
</html>
<?php } //end if?>

<?php
function returnajax($list){
    $ImageList = new ac_image_webp;
    echo $ImageList->do_list($list);
}
