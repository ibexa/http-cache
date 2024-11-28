@setup
Feature: Set system to desired state before tests
  
  @APIUser:admin
  Scenario: Set up the system to test translations
    Given Language "Polish" with code "pol-PL" exists
    And Language "French" with code "fre-FR" exists
    And I set configuration to "admin_group" siteaccess
      | key                          | value                |
      | languages                    | eng-GB,pol-PL,fre-FR |
    And I set configuration to "site" siteaccess
      | key                          | value                |
      | languages                    | eng-GB,pol-PL,fre-FR |

  @APIUser:admin
  Scenario: Set up the system to test caching of subrequests
    Given I create a "embeddedContentType" content type in "Content" with "embeddedContentType" identifier
      | Field Type                | Name      | Identifier | Required | Searchable | Translatable |
      | Text line                 | Name      | name	   | yes      | yes	       | yes          |
    And I create "embeddedContentType" Content items in root in "eng-GB"
      | name              |
      | EmbeddedItemNoEsi |
      | EmbeddedItemEsi   |
    And I create a "embeddingContentType_no_esi" content type in "Content" with "embeddingContentType_no_esi" identifier
      | Field Type                | Name      | Identifier | Required | Searchable | Translatable |
      | Text line                 | Name      | name	   | yes      | yes	       | yes          |
      | Content relation (single) | Relation  | relation   | yes      | no	       | yes          |
    And I create "embeddingContentType_no_esi" Content items in root in "eng-GB"
      | name               | relation           |
      | EmbeddingItemNoEsi | /EmbeddedItemNoEsi |
    And I create a "embeddingContentType_esi" content type in "Content" with "embeddingContentType_esi" identifier
      | Field Type                | Name      | Identifier | Required | Searchable | Translatable |
      | Text line                 | Name      | name	   | yes      | yes	       | yes          |
      | Content relation (single) | Relation  | relation   | yes      | no	       | yes          |
    And I create "embeddingContentType_esi" Content items in root in "eng-GB"
      | name             | relation         |
      | EmbeddingItemEsi | /EmbeddedItemEsi |
    And I set configuration to "ibexa.system.default.content_view"
    """
      full:
        embeddingContentType_no_esi:
            controller: Ibexa\Bundle\Behat\Controller\RenderController::embedAction
            template: "@IbexaBehat/tests/cache/embed_no_esi.html.twig"
            match:
                Identifier\ContentType: [embeddingContentType_no_esi]
        embeddingContentType_esi:
            controller: Ibexa\Bundle\Behat\Controller\RenderController::embedAction
            template: "@IbexaBehat/tests/cache/embed_esi.html.twig"
            match:
                Identifier\ContentType: [embeddingContentType_esi]
      line:
        embedded:
            controller: Ibexa\Bundle\Behat\Controller\RenderController::longAction
            template: "@IbexaBehat/tests/cache/embedded.html.twig"
            match:
                Identifier\ContentType: [embeddedContentType]
    """
