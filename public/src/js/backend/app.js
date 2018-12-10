var GSBackend = angular.module('gsBackend', ['ngRoute', 'ngResource', 'ngSanitize', 'ngTagsInput', 'ui.ace', 'angular-simditor'])
    .config(function($routeProvider) {
        $routeProvider
            .when('/users', {
                templateUrl: '/src/js/backend/templates/users/users.html',
                controller: 'UsersController'
            })
            .when('/users/:userId', {
                templateUrl: '/backend/user/single',
                controller: 'UserController'
            })
            .when('/blog', {
                templateUrl: '/src/js/backend/templates/blog/blog.html',
                controller: 'BlogController'
            })
            .when('/blog/:blogId', {
                templateUrl: '/src/js/backend/templates/blog/blog_single.html',
                controller: 'BlogController'
            })
            .when('/menu', {
                templateUrl: '/src/js/backend/templates/menu/menu.html',
                controller: 'MenuController'
            })
            .when('/menu/:menuId', {
                templateUrl: '/src/js/backend/templates/menu/menu_single.html',
                controller: 'MenuController'
            })
            .when('/products', {
                templateUrl: '/src/js/backend/templates/products/products.html',
                controller: 'ProductsController'
            })
            .when('/products/:productId', {
                templateUrl: '/src/js/backend/templates/products/products_single.html',
                controller: 'ProductsController'
            })
            .when('/products/:productId/:variantId/', {
                templateUrl: '/src/js/backend/templates/products/variant_single.html',
                controller: 'VariantController'
            })
            .when('/support/', {
                templateUrl: '/src/js/backend/templates/support/support.html',
                controller: 'SupportController'
            })
            .when('/support/:ticketId', {
                templateUrl: '/src/js/backend/templates/support/support_single.html',
                controller: 'SupportController'
            })
            .when('/cms/', {
                templateUrl: '/src/js/backend/templates/cms/cms.html',
                controller: 'CmsController'
            })
            .when('/cms/:cmsId', {
                templateUrl: '/src/js/backend/templates/cms/cms_single.html',
                controller: 'CmsController'
            })
            .when('/gameserver/', {
                templateUrl: '/src/js/backend/templates/gameserver/gameserver.html',
                controller: 'GameserverController'
            })
            .when('/forum/', {
                templateUrl: '/src/js/backend/templates/forum/forum.html',
                controller: 'ForumController'
            })
            .when('/forum/:forumId', {
                templateUrl: '/src/js/backend/templates/forum/forum_single.html',
                controller: 'ForumController'
            });
    });

GSBackend.run();
