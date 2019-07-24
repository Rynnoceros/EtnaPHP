import DatabasesComponent from "./databases/Databases.vue"
import ListTablesComponent from "./tables/ListTables.vue"
import Vue from "vue";

export default {
    name: 'app',
    components: {
        "databases-component": DatabasesComponent,
        "list-tables-component": ListTablesComponent
    },
    data () {
      return {
        selected_database: "",
        selected_table: "",
        return_type: "success",
        message: null,
        dismiss_cpt: 0
      }
    },
    methods: {
        select_database: function(database_name) {
            this.selected_database = database_name;
        },
        decrease_cpt(dismiss_cpt) {
            this.dismiss_cpt = dismiss_cpt;
            if (dismiss_cpt <= 0) {
                this.message = null;
            }
        },
        show_message(event) {
            switch (Object.keys(event)[0]) {
                case "error" : this.return_type = "danger"; break;
                case "message" : this.return_type = "success"; break;
                default : this.return_type = "";
            }
            this.message = Object.values(event)[0];
            if (this.message)
                this.dismiss_cpt = 5;
        }
    }
}