<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 02.06.14
 * Time: 18:59
 */

namespace Wealthbot\SignatureBundle\Model\TabsConfiguration;


use Wealthbot\ClientBundle\Entity\Beneficiary;
use Wealthbot\SignatureBundle\Model\Tab\TextTab;
use Wealthbot\SignatureBundle\Model\TabCollection;

class BeneficiaryDesignationForm extends AbstractTabsConfiguration
{
    /** @var  \Wealthbot\ClientBundle\Entity\Beneficiary */
    private $beneficiary;

    public function __construct(Beneficiary $beneficiary)
    {
        $this->beneficiary = $beneficiary;
    }

    /**
     * Generate collection of tabs
     *
     * @return TabCollection
     */
    public function generate()
    {
        $clientAccount = $this->beneficiary->getClientAccount();
        $companyInformation = $clientAccount->getClient()->getRiaCompanyInformation();

        $tabs = array();

        $advisorCode = new TextTab();
        $advisorCode->setTabLabel('advisor#')->setValue($this->getAdvisorCode($companyInformation));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('account#')->setValue($clientAccount->getAccountNumber());
        $tabs[] = $accountNumber;

        $accountTitleTab = new TextTab();
        $accountTitleTab->setTabLabel('account_title')->setValue($clientAccount->getTypeString());
        $tabs[] = $accountTitleTab;

        $tabs = array_merge($tabs, $this->getBeneficiariesInformationTabs());

        return new TabCollection($tabs);
    }

    /**
     * @return array
     */
    private function getBeneficiariesInformationTabs()
    {
        $tabs = array();
        $primary = 0;
        $alternative = 0;

        /** @var Beneficiary[] $accountBeneficiaries */
        $accountBeneficiaries = $this->beneficiary->getAccount()->getBeneficiaries();
        foreach ($accountBeneficiaries as $beneficiary) {
            if ($beneficiary->isPrimary()) {
                $primary++;
                $prefix = 'beneficiary_' . $primary . '_';
            } else {
                $alternative++;
                $prefix = 'alternative_beneficiary_' . $alternative . '_';
            }

            $nameTab = new TextTab();
            $nameTab->setTabLabel($prefix . 'name')->setValue($beneficiary->getFullName());
            $tabs[] = $nameTab;

            $relationshipTab = new TextTab();
            $relationshipTab->setTabLabel($prefix . 'relationship')->setValue($beneficiary->getRelationship());
            $tabs[] = $relationshipTab;

            $birthDateTab = new TextTab();
            $birthDateTab->setTabLabel($prefix . 'birth_date')->setValue($beneficiary->getBirthDate()->format('m-d-Y'));
            $tabs[] = $birthDateTab;

            $ssnTab = new TextTab();
            $ssnTab->setTabLabel($prefix . 'ssn')->setValue($beneficiary->getSsn());
            $tabs[] = $ssnTab;

            $shareTab = new TextTab();
            $shareTab->setTabLabel($prefix . 'share')->setValue($beneficiary->getShare());
            $tabs[] = $shareTab;
        }

        return $tabs;
    }

} 