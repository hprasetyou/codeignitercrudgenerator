<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Import_spreadsheet
{
    public function get_template($model)
    {
        $CI =& get_instance();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $colls = $CI->schema->extract_fields($model);
        $except = array('Id','CreatedAt','UpdatedAt');
        $i = 0;
        foreach ($colls as $key => $coll) {
            # code...
            if (!in_array($coll['Name'], $except)) {
                if (!is_object($coll['Name'])) {
                    $sheet->setCellValue("{$this->toNum($i)}1", $coll['Name']);
                    $i++;
                } else {
                    //  $this->form[$coll['LocalName']] = $coll['LocalName'];
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $path = "public/assets/spreadsheet/$model.xlsx";
        $writer->save($path);
        return $path;
    }


    public function import($filepath,$objname)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
				$i = 0;
				$j = 2;
				$cols = [];


				while ($i >= 0) {
					if(strlen($worksheet->getCell("{$this->toNum($i)}1")->getValue()) > 0){
						$cols["{$this->toNum($i)}"] = $worksheet->getCell("{$this->toNum($i)}1")->getValue();
						$i++;
					}else{
						break;
					}
				}

				for ($j=2; $j < $worksheet->getHighestRow(); $j++) {
					// $j is row number
            $qq = $objname;
            $obj = new $qq;
						foreach ($cols as $index => $col) {
							# code...
							$f = "set$col";
              $obj->$f($worksheet->getCell("{$index}{$j}")->getValue());
						}
            $obj->save();
				}



        print_r($cols);
    }

    private function toNum($data)
    {
        $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $alpha_flip = array_flip($alphabet);
        if ($data <= 25) {
            return strtoupper($alphabet[$data]);
        } elseif ($data > 25) {
            $dividend = ($data + 1);
            $alpha = '';
            $modulo;
            while ($dividend > 0) {
                $modulo = ($dividend - 1) % 26;
                $alpha = $alphabet[$modulo] . $alpha;
                $dividend = floor((($dividend - $modulo) / 26));
            }
            return strtoupper($alpha);
        }
    }
}
