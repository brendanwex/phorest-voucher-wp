<?php
/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 02/11/2018
 * Time: 11:03
 */
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

class PhorestPdf extends TCPDF
{
    public function Header() {

        global $phorest;

        $voucher_settings = get_option("voucher_settings");
        isset($voucher_settings['voucher_bg_colour']) ? $voucher_bg_colour = $voucher_settings['voucher_bg_colour'] : $voucher_bg_colour = "#ffffff";
        isset($voucher_settings['voucher_font_colour']) ? $voucher_colour = $voucher_settings['voucher_font_colour'] : $voucher_colour = "#ffffff";
        isset($voucher_settings['voucher_logo']) ? $voucher_logo = $voucher_settings['voucher_logo'] : $voucher_logo = "";


        // Background color
        $this->Rect(0,0,210,297,'F','', $phorest->hex2RGB($voucher_bg_colour, false));

        $this->SetLineStyle( array( 'width' => 0.40, 'color' => $phorest->hex2RGB($voucher_colour, false)));

        $this->Line(5, 5, $this->getPageWidth()-5, 5);

        $this->Line($this->getPageWidth()-5, 5, $this->getPageWidth()-5,  $this->getPageHeight()-5);
        $this->Line(5, $this->getPageHeight()-5, $this->getPageWidth()-5, $this->getPageHeight()-5);
        $this->Line(5, 5, 5, $this->getPageHeight()-5);

        $this->Image($voucher_logo, 'C', 15, '', '50', 'png', false, 'C', true, 300, 'C', false, false, 0, false, false, false);
    }


    // Page footer
    public function Footer()
    {
        $voucher_settings = get_option("voucher_settings");
        isset($voucher_settings['voucher_footer']) ? $voucher_footer = $voucher_settings['voucher_footer'] : $voucher_footer = "";

        $this->SetY(-30);
        $this->SetFont('helvetica', 'I', 9);
        $this->writeHTML($voucher_footer, true, false, true, false, 'C');


    }


    // Colored table
    public function voucherTable($header,$data, $voucher_code){
        $voucher_settings = get_option("voucher_settings");
        isset($voucher_settings['voucher_bg_colour']) ? $voucher_bg_colour = $voucher_settings['voucher_bg_colour'] : $voucher_bg_colour = "#ffffff";
        isset($voucher_settings['voucher_font_colour']) ? $voucher_colour = $voucher_settings['voucher_font_colour'] : $voucher_colour = "#ffffff";
        isset($voucher_settings['voucher_banner']) ? $voucher_banner = $voucher_settings['voucher_banner'] : $voucher_banner = "";

        $css = "
        
        <style>
        
        .aligncenter{text-align: center;margin:auto;}
        
        table {
        font-size:10pt;
        
        }
        
        table th {
        border:1px solid $voucher_colour;
        color:$voucher_colour;
        width: 30%;
        text-align: left;
        }
        
        table td {
        border:1px solid $voucher_colour;
        color:#000;
        width:70%;

        }
        
        </style>";

        $output = "<table cellpadding=\"10\" border='1'>";


        $i = 0;

        foreach($header as $th){


            $output .= "<tr>";

            $output .= "<th>";

            $output .= $th;

            $output .= "</th>";


            $output .= "<td>";

            $output .= $data[$i];

            $output .= "</td>";

            $output .= "</tr>";


            $i++;
        }
        $output .= "<tr>";

        $output .= "<th>";

        $output .= "Voucher Code";

        $output .= "</th>";


        $output .= "<td>";

        $output .= $voucher_code;

        $output .= "</td>";

        $output .= "</tr>";

        $output .= "</table>";


        $margins = $this->getMargins();


        $this->SetLeftMargin(15);


        $this->writeHTMLCell(180, '', '', '80', $css.$output , 0, false, false, true, "C");

        $this->SetLeftMargin($margins['left']);


        if(!empty($voucher_banner)) {
            $this->Image($voucher_banner, 'C', 200, '180', '', 'png', false, 'C', true, 300, 'C', false, false, 0, false, false, false);
        }

        // define barcode style
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 10,
            'stretchtext' => 4
        );

        $this->write1DBarcode($voucher_code, 'C128', '85', '240', 120, 25, 0.4, $style, 'C');
    }


}