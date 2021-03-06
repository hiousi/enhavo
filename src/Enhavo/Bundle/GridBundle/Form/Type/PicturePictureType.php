<?php
/**
 * TextType.php
 *
 * @since 23/08/14
 * @author Gerhard Seidel <gseidel.message@googlemail.com>
 */

namespace Enhavo\Bundle\GridBundle\Form\Type;

use Enhavo\Bundle\GridBundle\Item\ItemFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PicturePictureType extends ItemFormType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array(
            'label' => 'form.label.title',
            'translation_domain' => 'EnhavoAppBundle',
            'translation' => $this->translation
        ));

        $builder->add('fileLeft', 'enhavo_files', array(
            'label' => 'picturePicture.form.label.picture_left',
            'translation_domain' => 'EnhavoGridBundle',
            'multiple' => false
        ));

        $builder->add('captionLeft', 'text', array(
            'label' => 'picturePicture.form.label.caption_left',
            'translation_domain' => 'EnhavoGridBundle',
            'translation' => $this->translation
        ));

        $builder->add('fileRight', 'enhavo_files', array(
            'label' => 'picturePicture.form.label.picture_right',
            'translation_domain' => 'EnhavoGridBundle',
            'multiple' => false
        ));

        $builder->add('captionRight', 'text', array(
            'label' => 'picturePicture.form.label.caption_right',
            'translation_domain' => 'EnhavoGridBundle',
            'translation' => $this->translation
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enhavo\Bundle\GridBundle\Entity\PicturePicture'
        ));
    }

    public function getName()
    {
        return 'enhavo_grid_item_picture_picture';
    }
} 