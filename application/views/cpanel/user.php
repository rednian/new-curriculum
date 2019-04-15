<script>
  (function(){
    window.Cpanel = {
      Views:{},
      Models:{},
      Router:{},
      Collections:{}
    };

    window.template = function(id) {
      return _.template( $('#'+id).html() );
    }


    /*
     |----------------------------------------------------------------------------------------------------------------------
     | Router
     |----------------------------------------------------------------------------------------------------------------------
    */
    Cpanel.Router = Backbone.Router.extend({
        routes:{
        '':'index'
        },
        
        index:function(){
        console.log('index...');
        }
    });
    
    /*
     |----------------------------------------------------------------------------------------------------------------------
     | User Model
     |----------------------------------------------------------------------------------------------------------------------
    */
    Cpanel.Models.User = Backbone.Model.extend({
      validate: function() {}
    });
    /*
     |----------------------------------------------------------------------------------------------------------------------
     | Users Collection
     |----------------------------------------------------------------------------------------------------------------------
    */
    Cpanel.Collections.Users = Backbone.Collection.extend({
      model: Cpanel.Models.User,
      url:'<?php echo base_url('cpanel/userList');?>',

      customUrl: function (method) {
        switch (method) {
          // case 'read':
            // return 'http://localhost:51377/api/Books/' + this.id;
            // break;
          // case 'create':
          //   return 'http://localhost:51377/api/Books';
          //   break;
          // case 'update':
          //   return 'http://localhost:51377/api/Books/' + this.id;
          //   break;
          // case 'delete':
          //   return 'http://localhost:51377/api/Books/' + this.id;
          //   break;
        }
      },

      sync: function (method, model, options) {
        options || (options = {});
        options.url = this.customUrl(method.toLowerCase());

        return Backbone.sync.apply(this, arguments);
      }
    });
    /*
     |----------------------------------------------------------------------------------------------------------------------
     | Global View
     |----------------------------------------------------------------------------------------------------------------------
    */
    Cpanel.Views.Cpanel = Backbone.View.extend({
      //el:
      //template: 
      initialize: function() {
        console.log('dfsdssdgdsg');
      },

      render: function() {
      },
    });
    /*
     |----------------------------------------------------------------------------------------------------------------------
     | Us
     |----------------------------------------------------------------------------------------------------------------------
    */


  })();

  new Cpanel.Router;
  Backbone.history.start();

  Cpanel.users = new Cpanel.Collections.Users;
  Cpanel.users.fetch().then(function() {
    new Cpanel.Views.Cpanel({ collection: Cpanel.users });
  });
</script>
