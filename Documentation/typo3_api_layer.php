TYPO3 API-Layer
===============
In TYPO3 ändert sich permanent die API. Das bedeutet jede Menge Arbeit für Entwickler, die ihre
Extension für mehrere Versionen von TYPO3 kompatibel halten wollen. rn_base bietet daher für viele
wichtige API-Funktionen passende Wrapper an. Diese leiten die Aufrufe dann passend zur TYPO3-Version
an die eigentliche Implementierung weiter.

Die folgende Liste zeigt die vorhandenen Methoden in rn_base:

rn_base | bis TYPO3 4.5 | ab TYPO3 6.x
------- | ------------- | ------------
tx_rnbase_util_Debug::debug() | t3lib_utility_Debug::debug() | t3lib_utility_Debug::debug()
tx_rnbase_util_Misc::getIndpEnv() | t3lib_div::getIndpEnv() | \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv()
tx_rnbase_util_Math::testInt() | t3lib_utility_Math::canBeInterpretedAsInteger() | t3lib_utility_Math::canBeInterpretedAsInteger()
tx_rnbase_util_Math::intInRange() 	t3lib_utility_Math::forceIntegerInRange() | t3lib_utility_Math::forceIntegerInRange()
tx_rnbase_util_TCA::loadTCA() | t3lib_div::loadTCA() | \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca()
tx_rnbase_util_TYPO3::getHttpUtilityClass() | t3lib_utility_Http | \TYPO3\\CMS\\Core\\Utility\\HttpUtility

**Hinweis:** Diese Liste ist nicht vollständig!
