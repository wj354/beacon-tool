<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Number;
use beacon\widget\RadioGroup;
use beacon\widget\Text;

#[Form(title: 'UpImage设置', template: 'form/field_support.tpl')]
class UpImage
{
    #[Text(
        label: '上传路径',
    )]
    public string $url = '/service/upload';

    #[RadioGroup(
        label: '按钮风格',
        options: [
        ['file', '单图'],
        ['fileGroup', '多图'],
    ],
        dynamic: [
            [
                'eq' => 'image',
                'hide' => 'size',
            ],
            [
                'eq' => 'imgGroup',
                'show' => 'size',
            ],
        ]
    )]
    public string $mode = 'image';

    #[Text(
        label: '支持的图片类型',
        attrs: [
            'style' => 'width:400px'
        ]
    )]
    public string $extensions = 'jpg,jpeg,bmp,gif,png';

    #[Text(
        label: '上传域名称',
    )]
    public string $fieldName = 'filedata';

    #[Number(
        label: '显示区宽',
    )]
    public int $imgWidth = 0;

    #[Number(
        label: '高',
        viewMerge: -1
    )]
    public int $imgHeight = 0;

    #[Number(
        label: '上传最大数量',
        prompt: '0为不限制数量',
    )]
    public int $size = 0;


    public function export(): array
    {
        return [
            'mode' => $this->mode,
            'extensions' => $this->extensions,
            'fieldName' => $this->fieldName,
            'imgWidth' => $this->imgWidth,
            'imgHeight' => $this->imgHeight,
            'size' => $this->size,
        ];
    }
}