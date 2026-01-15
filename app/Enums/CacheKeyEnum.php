<?php

declare(strict_types=1);

namespace App\Enums;

enum CacheKeyEnum: string
{
    case FEEDBACKS_TARGET = 'feedbacks_target_%s_%d_page_%s';
    case FEEDBACKS_SELLER = 'feedbacks_seller_%d_page_%s';

    case REVIEWS_USER = 'reviews_user_%d_%s_page_%d';

    case PRODUCTS_CATALOG = 'products_catalog_search_%s_page_%s_per_%d';
    case PRODUCTS_AUTOCOMPLETE = 'products_autocomplete_%s_limit_%d';
    case PRODUCTS_USER = 'products_user_%d_page_%s_per_%d';
    case PRODUCTS_RECOMMENDATIONS = 'products_recommendations_user_%d_excluded_%s';

    case USER_PERMISSIONS = 'user_permissions_%d';
    case USER_ROLES = 'user_roles_%d';

    case RECOMMENDATIONS_USER = 'recommendations_user_%d';

    case ANALYTICS_TOTAL_REVENUE = 'analytics_total_revenue';
    case ANALYTICS_SALES_BY_CURRENCY = 'analytics_sales_by_currency';

    case CURRENCY_RATES = 'currency_rates_%s';
}
