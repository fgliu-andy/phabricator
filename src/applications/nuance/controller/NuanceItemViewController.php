<?php

final class NuanceItemViewController extends NuanceController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $id = $request->getURIData('id');

    $item = id(new NuanceItemQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->executeOne();
    if (!$item) {
      return new Aphront404Response();
    }

    $title = pht('Item %d', $item->getID());

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      pht('Items'),
      $this->getApplicationURI('item/'));
    $crumbs->addTextCrumb($title);
    $crumbs->setBorder(true);

    $properties = $this->buildPropertyView($item);
    $curtain = $this->buildCurtain($item);

    $header = id(new PHUIHeaderView())
      ->setHeader($title);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setCurtain($curtain)
      ->addPropertySection(pht('DETAILS'), $properties);

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }

  private function buildPropertyView(NuanceItem $item) {
    $viewer = $this->getViewer();

    $properties = id(new PHUIPropertyListView())
      ->setUser($viewer);

    $properties->addProperty(
      pht('Date Created'),
      phabricator_datetime($item->getDateCreated(), $viewer));

    $source = $item->getSource();
    $definition = $source->getDefinition();

    $definition->renderItemViewProperties(
      $viewer,
      $item,
      $properties);

    return $properties;
  }

  private function buildCurtain(NuanceItem $item) {
    $viewer = $this->getViewer();
    $id = $item->getID();

    $can_edit = PhabricatorPolicyFilter::hasCapability(
      $viewer,
      $item,
      PhabricatorPolicyCapability::CAN_EDIT);

    $curtain = $this->newCurtainView($item);

    $curtain->addAction(
      id(new PhabricatorActionView())
        ->setName(pht('Manage Item'))
        ->setIcon('fa-cogs')
        ->setHref($this->getApplicationURI("item/manage/{$id}/")));

    return $curtain;
  }


}
