<?

namespace Beeralex\Core\Config;

class TabsBuilder
{
    /**
     * @var Tab[] $tabs
     */
    private $tabs = [];
    private $addedIdTabs;

    public function addTab(Tab $tab)
    {
        $id = $tab->getId();
        if($this->addedIdTabs[$id]){
            throw new \InvalidArgumentException("Таб с таким id ($id) уже был добавлен");
        }
        $this->addedIdTabs[$id] = true;
        $this->tabs[] = $tab;
        return $this;
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function getTabsFormattedArray()
    {
        $aTabs = [];

        foreach ($this->tabs as $tab) {
            $aTabs[] = [
                "DIV" => $tab->getId(),
                "TAB" => $tab->getName(),
                "TITLE" => $tab->getTitle(),
                "OPTIONS" => $tab->getOptionsFormattedArray()
            ];
        }
        return $aTabs;
    }
}
