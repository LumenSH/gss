(function() {
    "use strict";
    (function() {
        var ngSimditor = angular.module('angular-simditor', []);
        ngSimditor.constant('simditorConfig', {
            locale: 'en-US',
            toolbar: [
                'title',
                'bold',
                'italic',
                'underline',
                'strikethrough',
                'fontScale',
                'color',
                'ol',
                'ul',
                'blockquote',
                'code',
                'table',
                'link',
                'image',
                'hr',
                'indent',
                'outdent',
                'alignment',
                'emoji'
            ],
            emoji: {
                imagePath: '/src/img/emoji/'
            }
        });
        ngSimditor.directive('ngSimditor', ['$timeout', 'simditorConfig', function($timeout, simditorConfig) {
            return {
                scope: {
                    content: '='
                },
                restrict: 'E',
                template: '<textarea data-autosave="editor-content" autofocus></textarea>',
                replace: true,
                link: function($scope, iElm, iAttrs, controller) {
                    var editor = new Simditor(
                        angular.extend({textarea: iElm}, simditorConfig)
                    );

                    var nowContent = '';

                    $scope.$watch('content', function(value, old){
                        if(typeof value !== 'undefined' && value != nowContent){
                            editor.setValue(value);
                        }
                    });

                    editor.on('valuechanged', function(e){
                        if($scope.content != editor.getValue()){
                            $timeout(function(){
                                $scope.content = nowContent = editor.getValue();
                            });
                        }
                    });
                }
            };
        }]);
    })();
}).call(this);