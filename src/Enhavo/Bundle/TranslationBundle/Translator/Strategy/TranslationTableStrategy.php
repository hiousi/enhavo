<?php
/**
 * TranslationTableStrategy.php
 *
 * @since 27/11/16
 * @author gseidel
 */

namespace Enhavo\Bundle\TranslationBundle\Translator\Strategy;

use Enhavo\Bundle\TranslationBundle\Entity\Translation;
use Enhavo\Bundle\TranslationBundle\Metadata\Metadata;
use Enhavo\Bundle\TranslationBundle\Metadata\Property;
use Enhavo\Bundle\TranslationBundle\Translator\TranslationStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Enhavo\Bundle\TranslationBundle\Repository\TranslationRepository;

class TranslationTableStrategy implements TranslationStrategyInterface
{
    use ContainerAwareTrait;

    /**
     * @var Translation[]
     */
    private $updateRefIds = [];

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var string[]
     */
    private $locales = [];

    public function __construct($locales, $defaultLocale)
    {
        foreach($locales as $locale => $data) {
            $this->locales[] = $locale;
        }
        $this->defaultLocale = $defaultLocale;
    }


    public function storeValue($entity, Metadata $metadata, Property $property)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $values = $accessor->getValue($entity, $property->getName());
        if(is_array($values)) {
            $value = $this->storeValues($values, $entity, $property, $metadata);
            $accessor->setValue($entity, $property->getName(), $value);
        }
    }

    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    protected function storeValues(array $values, $entity, Property $property, Metadata $metadata)
    {
        $return = null;

        $repository = $this->getRepository();
        foreach($values as $locale => $value) {
            if($this->defaultLocale === $locale) {
                $return = $value;
            } else {
                $translation = $repository->findOneBy([
                    'class' => $metadata->getClass(),
                    'refId' => $entity->getId(),
                    'property' => $property->getName(),
                    'locale' => $locale
                ]);
                if($translation === null || $entity->getId() === null) {
                    $translation = new Translation();
                    $translation->setClass($metadata->getClass());
                    $translation->setRefId($entity->getId());
                    $translation->setProperty($property->getName());
                    $translation->setLocale($locale);
                    $this->updateRefIds[] = $translation;
                }
                $translation->setTranslation($value);
                $translation->setObject($entity);
            }
        }

        return $return;
    }

    /**
     * @return TranslationRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository('EnhavoTranslationBundle:Translation');
    }

    public function delete($entity, Metadata $metadata)
    {
        /** @var Translation[] $translations */
        $translations = $this->getRepository()->findBy([
            'class' => $metadata->getClass(),
            'refId' => $entity->getId()
        ]);
        foreach($translations as $translation) {
            $this->getEntityManager()->remove($translation);
        }
    }

    public function getTranslations($entity, Metadata $metadata, Property $property)
    {
        $data = [];
        $translations = null;
        if(is_object($entity) && method_exists($entity, 'getId')) {
            /** @var Translation[] $translations */
            $translations = $this->getRepository()->findBy([
                'class' => $metadata->getClass(),
                'refId' => $entity->getId(),
                'property' => $property->getName(),
            ]);
        } else {
            foreach($this->locales as $locale) {
                $data[$locale] = null;
            }
            return $data;
        }

        foreach($this->locales as $locale) {
            if($locale === $this->defaultLocale) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $value = $accessor->getValue($entity, $property->getName());
                $data[$locale] = $value;
                continue;
            }
            $value = null;
            foreach($translations as $translation) {
                if($translation->getLocale() === $locale) {
                    $value = $translation->getTranslation();
                    break;
                }
            }
            $data[$locale] = $value;
        }

        return $data;
    }

    public function updateReferences()
    {
        foreach($this->updateRefIds as $key => $translation) {
            $translation->setRefId($translation->getObject()->getId());
            $this->getEntityManager()->persist($translation);
            unset($this->updateRefIds[$key]);
        }
        $this->getEntityManager()->flush();
    }

    public function translate($entity, Metadata $metadata, Property $property, $locale)
    {
        if($locale === $this->defaultLocale) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        /** @var Translation $translation */
        $translation = $this->getRepository()->findOneBy([
            'class' => $metadata->getClass(),
            'refId' => $entity->getId(),
            'property' => $property->getName(),
            'locale' => $locale
        ]);
        $value = '';
        if($translation !== null && $translation->getTranslation() !== null) {
            $value = $translation->getTranslation();
        }
        $accessor->setValue($entity, $property->getName(), $value);
    }
}