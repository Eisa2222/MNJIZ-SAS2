<?php

namespace App\Helpers;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\SimpleType\Jc;

class ExportHelper
{
  protected $phpWord;
  protected $section;
  protected $document;

  public function __construct()
  {
    $this->phpWord = new PhpWord();

    // إعداد الخصائص الأساسية للمستند
    $this->phpWord->setDefaultFontName('Arial');
    $this->phpWord->setDefaultFontSize(12);

    // إعداد اتجاه الصفحة وهوامشها
    $this->section = $this->phpWord->addSection([
      'orientation' => 'portrait',
      'marginTop' => 0,
      'marginRight' => 0,
      'marginBottom' => 0,
      'marginLeft' => 0,
      'rtl' => true
    ]);
  }

  public function addHeader($logo = null, $offerNumber = '')
  {
    // إضافة شعار الشركة
    if ($logo) {
      $header = $this->section->addHeader();
      $header->addImage(
        storage_path('app/public/' . $logo),
        ['width' => 100, 'alignment' => Jc::CENTER]
      );
    }

    // إضافة رقم العرض
    if ($offerNumber) {
      $this->section->addText(
        'رقم العرض: ' . $offerNumber,
        ['rtl' => true, 'size' => 12, 'bold' => true],
        ['alignment' => Jc::END, 'spaceAfter' => 200]
      );
    }
  }

  public function addTitle($title)
  {
    $this->section->addText(
      $title,
      ['rtl' => true, 'size' => 16, 'bold' => true],
      ['alignment' => Jc::CENTER, 'spaceAfter' => 300]
    );
  }

  public function addSection($title, $content)
  {
    // إضافة عنوان القسم
    $this->section->addText(
      $title,
      ['rtl' => true, 'size' => 14, 'bold' => true],
      ['alignment' => Jc::START, 'spaceAfter' => 200]
    );

    // إضافة محتوى القسم
    if (is_array($content)) {
      foreach ($content as $index => $item) {
        $this->section->addText(
          ($index + 1) . '. ' . $item,
          ['rtl' => true, 'size' => 12],
          ['alignment' => Jc::START, 'spaceAfter' => 120]
        );
      }
    } else {
      $this->section->addText(
        $content,
        ['rtl' => true, 'size' => 12],
        ['alignment' => Jc::START, 'spaceAfter' => 120]
      );
    }
  }

  public function addSignatures($signatures, $seal = null)
  {
    $table = $this->section->addTable(['borderSize' => 0]);
    $row = $table->addRow();

    foreach ($signatures as $signature) {
      if (isset($signature['image'])) {
        $cell = $row->addCell(2000);
        $cell->addImage(
          storage_path('app/public/' . $signature['image']),
          ['width' => 70, 'height' => 70]
        );
        $cell->addText(
          $signature['name'],
          ['rtl' => true, 'size' => 10],
          ['alignment' => Jc::CENTER]
        );
      }
    }

    // إضافة الختم
    if ($seal) {
      $cell = $row->addCell(2000);
      $cell->addImage(
        storage_path('app/public/' . $seal),
        ['width' => 100, 'height' => 100]
      );
    }
  }

  public function addFooter($image = null, $pageNumbers = true)
  {
    $footer = $this->section->addFooter();

    if ($image) {
      $footer->addImage(
        storage_path('app/public/' . $image),
        ['width' => 500, 'alignment' => Jc::CENTER]
      );
    }

    if ($pageNumbers) {
      $footer->addPreserveText(
        'الصفحة {PAGE} من {NUMPAGES}',
        ['rtl' => true, 'size' => 10],
        ['alignment' => Jc::CENTER]
      );
    }
  }

  public function save($fileName)
  {
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->phpWord, 'Word2007');
    $objWriter->save($fileName);
  }
}