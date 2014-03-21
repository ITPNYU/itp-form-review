ifrApp.controller('ReviewCtrl', function ($scope, $http) {
  $http.get(ifr_api + 'review?blog=2') // FIXME
    .success(function(data) {
      $scope.reviews = data.objects;
    });

  $scope.getReviews = function(entry) {
    var entryReviews = [];
    for (var rIndex in $scope.reviews) {
      if ($scope.reviews[rIndex]['entry'] === entry) {
        entryReviews.push($scope.reviews[rIndex]);
      }
    }
    return entryReviews;
  };

  $scope.submitReview = function(formId, entry, formData) {
    //console.dir(formData);
    formData['form_id'] = formId;
    formData['id'] = id;
    $http.post(ifr_api + 'review?blog=2', formData) // FIXME
      .success(function(data, status, headers, config) {
        console.log('success');
      })
      .error(function() {
        console.log('error');
      });
  };
});
