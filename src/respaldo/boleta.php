<?php
 session_start();
//ob_start();
$mes_bol=$_SESSION['mes_bol'];
$ano_bol=$_SESSION['ano_bol'];
$num_bol=$_SESSION['num_bol'];
// Store the file name into variable
//"D:\AppServ\www\EugBoleta\PDF\Termica\15450158-4\2025-02\15450158-4_39_240_firmado_Termica.pdf"
$file = "../../EugBoleta/PDF/Termica/15450158-4/".$ano_bol."-".$mes_bol."/15450158-4_39_".$num_bol."_firmado_Termica.pdf";
//$filename = "../../EugBoleta/PDF/Termica/15450158-4/2025-02/15450158-4_39_240_firmado_Termica.pdf";
$filename = "../../EugBoleta/PDF/Termica/15450158-4/".$ano_bol."-".$mes_bol."/15450158-4_39_".$num_bol."_firmado_Termica.pdf";

  //$filepdf="../../../salida/".$carpeta."/pdf/".$dteInfo.$result['tipo_documento'].$dteInfo2.$result['numero'].".pdf";
// Header content type
header('Content-type: application/pdf');
  
header('Content-Disposition: inline; filename="' . $filename . '"');
  
header('Content-Transfer-Encoding: binary');
  
header('Accept-Ranges: bytes');
//  echo $file ;
// Read the file
@readfile($file);
  
?>