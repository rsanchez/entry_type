<?php

use eecli\Command\AbstractCreateFieldCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldEntryTypeCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create an Entry Type field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'entry_type';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'fieldtype',
                null,
                InputOption::VALUE_REQUIRED,
                'select or radio',
                'select',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'entry_type_fieldtype' => $this->option('fieldtype'),
        );
    }
}