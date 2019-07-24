import DatabaseStatsComponent from "./DatabaseStats.vue"

export default {
    name: "DatabasesComponent",
    props: ["selected_database"],
    components: {
        "database-stats": DatabaseStatsComponent
    },
    data() {
        return {
            databases: [],
            selected_index: null,
            database_to_add: '',
            database_to_remove: '',
            index_to_remove: null,
            added_state: null,
            error_msg: null,
            edit_mode: false,
        }
    },
    created() {
        this.get_databases();
    },
    methods: {
        get_databases: function() {
            fetch("http://rynnoceros.etnaformation.local/backend/databases/get_databases.php")
            .then(response => response.json())
            .then(databases => {
                this.databases = [];
                for (var i = 0; i < databases.data.length; ++i) {
                    this.databases.push(databases.data[i]);
                }
            });
        },
        select_database: function(database_name, index) {
            this.selected_index = index;
            this.$emit('databaseChanged', database_name);
        },
        add_database: function() {
            if (this.database_to_add.length > 0) {
                fetch("http://rynnoceros.etnaformation.local/backend/databases/create_database.php", {
                    method: "POST",
                    body: JSON.stringify({ "database_name" : this.database_to_add })
                })
                .then(response => response.json())
                .then(json_data => {
                    if (json_data.message !== undefined) {
                        this.databases.unshift({ "database_name" : this.database_to_add });
                        this.database_to_add = '';
                        this.$emit("returnEvent", json_data);
                        added_state = null;
                    } else {
                        this.added_state = 'invalid';
                        this.error_msg = json_data.error;
                        this.$emit("returnEvent", json_data);
                    }
                });
            }
        },
        rename_database: function(new_name) {
            fetch("http://rynnoceros.etnaformation.local/backend/databases/rename_database.php", {
                method: "POST",
                body: JSON.stringify({"database_name" : this.selected_database,
                                      "new_name" : new_name})
            })
            .then(response => response.json())
            .then(json_data => {
                if (json_data.message !== undefined) {                
                    this.edit_mode = false;
                } else {
                    this.get_databases();
                }
                this.$emit("returnEvent", json_data);
            });
        },
        drop_database: function() {
            fetch("http://rynnoceros.etnaformation.local/backend/databases/drop_database.php", {
                method: "POST",
                body: JSON.stringify({"database_name" : this.database_to_remove})
            })
            .then(response => response.json())
            .then(json_data => {
                if (json_data.message !== undefined) {
                    this.databases.splice(this.index_to_remove, 1);
                    this.$emit('databaseChanged', null);
                }
                this.$emit("returnEvent", json_data);
            });
            this.$refs.modalConfirm.hide();
        },
        cancel: function() {
            this.$refs.modalConfirm.hide();
        }
    },
}