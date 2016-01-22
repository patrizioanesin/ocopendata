<?php /* #?ini charset="utf-8"?

[ApiProvider]
ProviderClass[opendata]=OCOpenDataProvider

[RouteSettings]
SkipFilter[]=ezpRestContentController_*
SkipFilter[]=OCOpenDataController_*
SkipFilter[]=OCOpenDataController2_anonymousRead;2
SkipFilter[]=OCOpenDataController2_anonymousSearch;2
SkipFilter[]=OCOpenDataController2_anonymousBrowse;2

[OCOpenDataController2_CacheSettings]
ApplicationCache=disabled

[OCOpenDataController_datasetList_CacheSettings]
ApplicationCache=disabled

[OCOpenDataController_datasetView_CacheSettings]
ApplicationCache=disabled

[Authentication]
RequireAuthentication=enabled
AuthenticationStyle=ezpRestBasicAuthStyle
DefaultUserID=

*/ ?>
