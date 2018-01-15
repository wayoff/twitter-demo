<?php
  session_start();
  $accessToken = !empty($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Twitter Timeline Search</title>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.2/css/bulma.min.css">
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <section class="hero is-primary">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">
          Twitter Demo
        </h1>
        <h2 class="subtitle">
          Search user's last 500 tweets and make an histogram
        </h2>
      </div>
    </div>
  </section>

  <?php if(empty($accessToken)): ?>
    <div class="container">
      <div class="notification">
        Please authorize twitter first before using this application
        <br/ > 
        <a href="/authorize.php" class="button is-link">
          <span class="icon">
            <i class="fa fa-twitter"></i>
          </span>
          <span>Twitter</span>
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="container">
      <form id="form--search" style="margin-top: 10px">
        <div class="field has-addons">
          <p class="control has-icons-left is-expanded">
            <input class="input is-medium" type="text" placeholder="Twitter Screen name" required id="screenName">
            <span class="icon is-medium is-left">
              <i class="fa fa-twitter"></i>
            </span>
          </p>
          <p class="control">
            <button type="submit" class="button is-medium">

              <i class="fa fa-spinner fa-spin fa-fw" id="loading" style="display: none;">
                <span class="sr-only">Loading...</span>
              </i>

              Search
            </button>
          </p>
        </div>
      </form>

      <canvas id="canvas"></canvas>
    </div>
  <?php endif; ?>

  <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.17.1/axios.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>

  <script type="text/javascript">
    var GRAPH = null;
    var form = document.getElementById('form--search');
    var loading = document.getElementById('loading');

    var onReadyForStats = function(items) {
      var data = [];
      var result = [];

      items.forEach( function(item) {
        data.push({
          hour: moment(item.created_at).hour(),
          retweetCount: item.retweet_count
        });
      });

      // sort by hour
      data.sort( function(a, b) {
        if (a.hour < b.hour) {
          return -1;
        }

        if (a.hour > b.hour) {
          return 1;
        }

        return 0;

      });

      data.forEach( function(item) {
        if (!result[item.hour]) {
          result[item.hour] = {
            hour: item.hour,
            post: 1,
            retweetCount: item.retweetCount
          }

          return;
        }


        result[item.hour] = {
          hour: item.hour,
          post: result[item.hour].post + 1,
          retweetCount: item.retweetCount + result[item.hour].retweetCount
        }

      });

      console.log(result)

      return result;
    };

    var onPublishHistogram = function(items) {
      var labels = [];
      var post = [];

      items.forEach( function(item) {
        if (item.hour < 9) {
          labels.push( '0' + item.hour + ':00');
        } else {
          labels.push(item.hour + ':00');
        }

        post.push(item.post);
      });

      var ctx = document.getElementById("canvas").getContext("2d");

      var config = {
              type: 'line',
              data: {
                  labels: labels,
                  datasets: [{
                      label: "Activity",
                      backgroundColor: 'white',
                      borderColor: 'red',
                      data: post,
                      fill: false,
                  }]
              },
              options: {
                  responsive: true,
                  title:{
                      display:true,
                      text:'Histogram'
                  },
                  tooltips: {
                      mode: 'index',
                      intersect: false,
                  },
                  hover: {
                      mode: 'nearest',
                      intersect: true
                  },
                  scales: {
                      xAxes: [{
                          display: true,
                          scaleLabel: {
                              display: true,
                              labelString: 'Hours'
                          }
                      }],
                      yAxes: [{
                          display: true,
                          scaleLabel: {
                              display: true,
                              labelString: 'Post'
                          }
                      }]
                  }
              }
          };

      if (GRAPH) {
        GRAPH.destroy();
        GRAPH = null;
      }

      GRAPH = new Chart(ctx, config);
    };

    var onSearchTweet = function(e) {
      var name = document.getElementById('screenName').value;
      loading.style.display = 'block';

      axios.get('/tweets.php?user=' + name).then( function(response) {
        loading.style.display = 'none';
        var data = response.data;
        var stats = onReadyForStats(response.data);

        onPublishHistogram(stats);

      }).catch( function(error) {
        loading.style.display = 'none';
        console.log(error);
        alert('Something went wrong. Please try again');
      });

      e.preventDefault();
    };

    window.randomScalingFactor = function() {
      return Math.round(Math.random(-100, 100));
    };

    if (form) {
      form.addEventListener('submit', onSearchTweet)
    }
  </script>
</body>
</html>