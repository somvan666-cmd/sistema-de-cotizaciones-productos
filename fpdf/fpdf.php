<?php
/*******************************************************************************
* FPDF - Simple PDF Library                                                   *
* Simplified version for quotation system                                     *
*******************************************************************************/

class FPDF
{
    protected $page = 0;
    protected $n = 2;
    protected $buffer = '';
    protected $pages = array();
    protected $state = 0;
    protected $compress = true;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $w, $h;
    protected $wPt, $hPt;
    protected $lMargin, $tMargin, $rMargin, $bMargin;
    protected $cMargin;
    protected $x, $y;
    protected $lasth;
    protected $LineWidth;
    protected $FontFamily = '';
    protected $FontStyle = '';
    protected $FontSizePt = 12;
    protected $FontSize;
    protected $CurrentFont;
    protected $fonts = array();
    protected $DrawColor = '0 G';
    protected $FillColor = '0 g';
    protected $TextColor = '0 g';
    protected $ColorFlag = false;
    protected $ws = 0;
    protected $AutoPageBreak;
    protected $PageBreakTrigger;
    protected $InHeader = false;
    protected $InFooter = false;
    protected $offsets = array();
    
    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->fonts = array();
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        $this->offsets = array();
        
        // Scale factor
        if($unit=='mm')
            $this->k = 72/25.4;
        else
            $this->k = 72/25.4; // Default to mm
            
        // Page size
        if($size=='A4')
        {
            $this->w = 595.28;
            $this->h = 841.89;
        }
        elseif(is_array($size))
        {
            $this->w = $size[0]*$this->k;
            $this->h = $size[1]*$this->k;
        }
        else
        {
            $this->w = 595.28;
            $this->h = 841.89;
        }
        
        // Page orientation
        $orientation = strtolower($orientation);
        if($orientation=='p')
        {
            $this->DefOrientation = 'P';
        }
        else
        {
            $this->DefOrientation = 'L';
            $temp = $this->w;
            $this->w = $this->h;
            $this->h = $temp;
        }
        
        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w;
        $this->hPt = $this->h;
        $this->w = $this->w/$this->k;
        $this->h = $this->h/$this->k;
        
        // Page margins (1 cm)
        $margin = 28.35/$this->k;
        $this->SetMargins($margin, $margin);
        $this->cMargin = $margin/10;
        $this->LineWidth = .567/$this->k;
        $this->SetAutoPageBreak(true, 2*$margin);
        $this->SetFont('Arial', '', 12);
    }
    
    function SetMargins($left, $top, $right=null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }
    
    function SetAutoPageBreak($auto, $margin=0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h-$margin;
    }
    
    function SetFont($family, $style='', $size=0)
    {
        if($family=='')
            $family = $this->FontFamily;
        else
            $family = strtolower($family);
        $style = strtoupper($style);
        if($size==0)
            $size = $this->FontSizePt;
            
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        
        // Simple font metrics for Arial
        $this->CurrentFont = array(
            'cw' => array_fill(0, 256, 600), // Simple character widths
            'up' => -100,
            'ut' => 50
        );
        
        if($this->page>0)
            $this->_out(sprintf('BT /F1 %.2F Tf ET', $this->FontSizePt));
    }
    
    function GetStringWidth($s)
    {
        $s = (string)$s;
        $w = 0;
        $l = strlen($s);
        for($i=0; $i<$l; $i++)
            $w += 600; // Simple width calculation
        return $w*$this->FontSize/1000;
    }
    
    function AddPage($orientation='')
    {
        if($this->state==3)
            return;
            
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $fontsize = $this->FontSizePt;
        
        if($this->page>0)
        {
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            $this->_endpage();
        }
        
        $this->_beginpage($orientation);
        $this->_out('2 J');
        $this->LineWidth = .567/$this->k;
        $this->_out(sprintf('%.2F w', $this->LineWidth*$this->k));
        
        if($family)
            $this->SetFont($family, $style, $fontsize);
            
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
    }
    
    function Header()
    {
        // To be implemented in inherited class
    }
    
    function Footer()
    {
        // To be implemented in inherited class
    }
    
    function PageNo()
    {
        return $this->page;
    }
    
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AutoPageBreak)
        {
            $x = $this->x;
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
            
        $s = '';
        if($fill || $border==1)
        {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        
        if(is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
            if(strpos($border,'T')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
            if(strpos($border,'R')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
            if(strpos($border,'B')!==false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        }
        
        if($txt!=='')
        {
            if($align=='R')
                $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')
                $dx = ($w-$this->GetStringWidth($txt))/2;
            else
                $dx = $this->cMargin;
                
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, $this->_escape($txt));
            if($this->ColorFlag)
                $s .= ' Q';
        }
        
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0)
        {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }
    
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
            
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
            
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $this->Cell($w, $h, substr($s, $j, $i-$j), $border, 2, $align, $fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += 600; // Simple character width
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    $this->Cell($w, $h, substr($s, $j, $i-$j), $border, 2, $align, $fill);
                }
                else
                {
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), $border, 2, $align, $fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        
        if($i!=$j)
            $this->Cell($w, $h, substr($s, $j), $border, 2, $align, $fill);
        $this->x = $this->lMargin;
    }
    
    function Ln($h=null)
    {
        $this->x = $this->lMargin;
        if($h===null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }
    
    function GetX()
    {
        return $this->x;
    }
    
    function SetX($x)
    {
        if($x>=0)
            $this->x = $x;
        else
            $this->x = $this->w+$x;
    }
    
    function GetY()
    {
        return $this->y;
    }
    
    function SetY($y, $resetX=true)
    {
        if($y>=0)
            $this->y = $y;
        else
            $this->y = $this->h+$y;
        if($resetX)
            $this->x = $this->lMargin;
    }
    
    function SetXY($x, $y)
    {
        $this->SetY($y, false);
        $this->SetX($x);
    }
    
    function Output($dest='', $name='', $isUTF8=false)
    {
        if($this->state<3)
            $this->Close();
        if($dest=='')
            $dest = 'I';
        if($name=='')
            $name = 'doc.pdf';
            
        switch(strtoupper($dest))
        {
            case 'I':
                if(PHP_SAPI!='cli')
                {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="'.$name.'"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo $this->buffer;
                break;
            case 'D':
                header('Content-Type: application/x-download');
                header('Content-Disposition: attachment; filename="'.$name.'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                file_put_contents($name, $this->buffer);
                break;
            case 'S':
                return $this->buffer;
            default:
                throw new Exception('Incorrect output destination: '.$dest);
        }
        return '';
    }
    
    function Close()
    {
        if($this->state==3)
            return;
        if($this->page==0)
            $this->AddPage();
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        $this->_endpage();
        $this->_enddoc();
    }
    
    protected function _beginpage($orientation)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
    }
    
    protected function _endpage()
    {
        $this->state = 1;
    }
    
    protected function _escape($s)
    {
        return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
    }
    
    protected function _out($s)
    {
        if($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }
    
    protected function _enddoc()
    {
        $this->state = 3;
        $this->_putpages();
        $this->_putresources();
        $this->_putinfo();
        $this->_putcatalog();
        $this->_puttrailer();
        $this->buffer = "%PDF-1.3\n".$this->buffer;
    }
    
    protected function _putpages()
    {
        $nb = $this->page;
        for($n=1; $n<=$nb; $n++)
        {
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->wPt, $this->hPt));
            $this->_out('/Resources 2 0 R');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            
            $this->_newobj();
            $this->_out('<</Length '.strlen($this->pages[$n]).'>>');
            $this->_out('stream');
            $this->_out($this->pages[$n]);
            $this->_out('endstream');
            $this->_out('endobj');
        }
        
        $this->_newobj(1);
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for($i=0; $i<$nb; $i++)
            $kids .= (3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out('>>');
        $this->_out('endobj');
    }
    
    protected function _putresources()
    {
        $this->_newobj(2);
        $this->_out('<</Font <</F1 <</Type /Font /Subtype /Type1 /BaseFont /Arial>>>>');
        $this->_out('>>');
        $this->_out('endobj');
    }
    
    protected function _putinfo()
    {
        $this->_newobj();
        $this->_out('<</Producer (FPDF Simple)');
        $this->_out('/CreationDate (D:'.date('YmdHis').')>>');
        $this->_out('endobj');
    }
    
    protected function _putcatalog()
    {
        $this->_newobj();
        $this->_out('<</Type /Catalog');
        $this->_out('/Pages 1 0 R>>');
        $this->_out('endobj');
    }
    
    protected function _puttrailer()
    {
        $this->_out('xref');
        $this->_out('0 '.($this->n+1));
        $this->_out('0000000000 65535 f ');
        for($i=1; $i<=$this->n; $i++)
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
        $this->_out('trailer');
        $this->_out('<</Size '.($this->n+1));
        $this->_out('/Root '.$this->n.' 0 R');
        $this->_out('/Info '.($this->n-1).' 0 R>>');
        $this->_out('startxref');
        $this->_out(strlen($this->buffer));
        $this->_out('%%EOF');
    }
    
    protected function _newobj($n=null)
    {
        if($n===null)
            $n = ++$this->n;
        $this->offsets[$n] = strlen($this->buffer);
        $this->_out($n.' 0 obj');
    }
}
?>
