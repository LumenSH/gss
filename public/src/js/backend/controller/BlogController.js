GSBackend.controller('BlogController', ['$scope', '$routeParams', '$location', '$http', 'Blog', function($scope, $routeParams, $location, $http, Blog) {
    $scope.name = "BlogController";
    $scope.tab = 'de';
    $scope.editorOptions = {
        lineNumbers: true
    };

    $scope.tags = [];

    $http.get('/backend/blog/tags').then(function(response) {
       $scope.tags = response.data;
    });

    $scope.loadTags = function() {
        return $scope.tags.data;
    };

    $scope.saveBlog = function() {
        Blog.save($scope.blog.data, function(response) {
            if(typeof $scope.uploadForm !== 'undefined') {
                $scope.uploadForm.append('id', response.id);

                $http.post('/backend/blog/saveImage', $scope.uploadForm, {
                    withCredentials: true,
                    headers: {'Content-Type': undefined },
                    transformRequest: angular.identity
                }).then(function() {
                    gsAlert('success', 'Blog', 'Beitrag wurde erfolgreich gespeichert');
                    $location.path('/blog');
                });
            } else {
                gsAlert('success', 'Blog', 'Beitrag wurde erfolgreich gespeichert');
                $location.path('/blog');
            }
        });
    };

    $scope.editBlog = function(news) {
        $location.path('/blog/' + news.id);
    };

    $scope.deleteBlog = function(news) {
        Blog.delete({
            id: news.id
        }, function() {
            toastr.success("Beitrag wurde erfolgreich gelöscht");
            $scope.blogData.data.splice($scope.blogData.data.indexOf(news), 1);
            $location.path('/blog/');
        });
    };

    $scope.uploadFile = function(files) {
        $scope.uploadForm = new FormData();

        $scope.uploadForm.append("img", files[0]);
    };

    if(typeof $routeParams.blogId === 'undefined') {
        $scope.addBlog = function() {
            $location.path('/blog/new');
        };

        $scope.blogData = Blog.get();
    } else {
        if($routeParams.blogId === 'new') {
            $scope.title = 'Neuer Beitrag';
            $scope.news = {
                data: {
                    ID: null,
                    title_de: '',
                    title_en: '',
                    content_de: '',
                    content_en: '',
                    date: Math.floor(Date.now() / 1000)
                }
            }
        } else {
            $scope.blog = Blog.get({
                filter: [{
                    ID: $routeParams.blogId
                }]
            }, function() {
                var data = $scope.blog.data[0];
                $scope.blog = {
                    data: data
                };
                $scope.title = data.title_de + " bearbeiten";
            });
        }
    }
}]);