export default {
    name: "ListTablesComponent",
    props: ["selected_database"],
    data () {
        return {
            tables: [],
            selected_table: null,
            selected_index: null,
            elements: [],
            fields: [],
            columns: [],
            column_fields: [],
            edit_mode: false,
            old_name: null,
            index_to_remove: null,
            index_to_edit: null,
            column_to_remove: null,
            column_to_edit: null,
            column_to_edit_type: null,
            table_to_rename: false,
            column_to_add: false,
            new_table_name: null,
            tooltip: null,
            new_column: null,
            new_type: null,
            query: null,
            query_elements: [],
            adding_row: false,
            object_to_add:null,
            modifying_row: false,
            object_to_modify:null
        }
    },
    watch: {
        selected_database : {
            immediate: true,
            handler(newValue, oldValue) {
                this.selected_table = null;
            }
        }
    },
    methods: {
        get_tables() {
            if (this.selected_database) {
                fetch("http://rynnoceros.etnaformation.local/backend/tables/get_tables.php/?database_name="+this.selected_database)
                .then(response => response.json())
                .then(json_data => {
                    this.tables = [];
                    if (json_data.data !== undefined) {
                        for (var i = 0; i < json_data.data.length; ++i) {
                            this.tables.push(json_data.data[i]);
                        }
                    } else {
                        this.$emit("returnEvent", json_data);
                    }
                });
            }
        },
        select_table(table_name, index) {
            this.selected_table = table_name;
            this.selected_index = index;
            this.get_elements();
            this.get_columns();
            this.$emit("selectedTableChanged", table_name);
        },
        get_elements() {
            if (this.selected_database && this.selected_table) {
                this.show_stats();
                fetch("http://rynnoceros.etnaformation.local/backend/tables/select_rows.php/?database_name="+
                    this.selected_database+"&table_name="+this.selected_table)
                .then(response => response.json())
                .then(json_data => {
                    this.fields = [];
                    if (json_data.data !== undefined) {
                        this.elements = json_data.data;
                        this.fields = [{ key:"remove", label:"-"}, "edit", "index"].concat(Object.keys(json_data.data[0]));
                    } else {
                        this.$emit("returnEvent", json_data);
                    }
                });
            }
        },
        get_columns() {
            if (this.selected_database && this.selected_table) {
                fetch("http://rynnoceros.etnaformation.local/backend/tables/show_columns.php/?database_name="+
                this.selected_database+"&table_name="+this.selected_table)
                .then(response => response.json())
                .then(json_data => {
                    if (json_data.data !== undefined) {
                        this.columns = json_data.data;
                        this.column_fields = [{ key: "remove_column", label: "-" }, { key: "edit_column", label: "-" }].concat(Object.keys(json_data.data[0]));
                    } else {
                        this.$emit("returnEvent", json_data);
                    }
                });
            }
        },
        cancel() {
            this.$refs.rowConfirm.hide();
        },
        cancel_delete() {
            this.$refs.deleteColumn.hide();
        },
        delete_row() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/delete_from_table.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database,
                                      "table_name": this.selected_table, 
                                      "criterias" : this.elements[this.index_to_remove]})
            })
            .then(response => response.json())
            .then(json_data => {
                this.$refs.rowConfirm.hide();
                this.get_elements();
                this.$emit("returnEvent", json_data);
            })
        },
        delete_column() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/drop_column.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database,
                                        "table_name": this.selected_table,
                                        "column_name": this.column_to_remove })
            })
            .then(response => response.json())
            .then(json_data => {
                this.$refs.deleteColumn.hide();
                this.get_columns();
                this.get_elements();
                if (json_data.message !== undefined) {
                    this.column_to_remove = null;
                }   
                this.$emit("returnEvent", json_data);
            });
        },
        add_column() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/add_column.php", {
                method: "POST",
                body: JSON.stringify( { "database_name": this.selected_database,
                                        "table_name": this.selected_table,
                                        "column_name": this.new_column,
                                        "column_type": this.new_type })
            })
            .then(response => response.json())
            .then(json_data => {
                console.log(json_data);
                if (json_data.message !== undefined) {
                    this.get_columns();
                    this.get_elements();
                    this.new_column = null;
                    this.new_type = null;
                }
                this.$emit("returnEvent", json_data);
            });
        },
        update_column(new_name, new_type) {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/change_column.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database,
                                        "table_name": this.selected_table,
                                        "column_name": this.old_name,
                                        "new_name": new_name,
                                        "column_type": new_type })
            })
            .then(response => response.json())
            .then(json_data => {
                if (json_data.message !== undefined) {
                    this.get_columns();
                    this.get_elements();
                    this.column_to_edit = null;
                    this.column_to_edit_type = null;
                } 
                this.$emit("returnEvent", json_data);
            });
        },
        rename_table(new_name) {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/rename_table.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database, 
                                        "table_name": this.selected_table, "new_name" : new_name})
            })
            .then(response => response.json())
            .then(json_data => {
                if (json_data.message !== undefined) {
                    this.selected_table = new_name;
                    this.table_to_rename = false;
                } else {
                    this.$emit("selectedTableChanged", new_name);
                    this.get_tables();
                }
                this.$emit("returnEvent", json_data);
            });
        },
        show_stats() {
            if (this.selected_database && this.selected_table) {
                fetch("http://rynnoceros.etnaformation.local/backend/tables/show_stats.php/?database_name=" + this.selected_database +
                        "&table_name=" + this.selected_table)
                .then(response => response.json())
                .then(json_data => {
                    this.tooltip = json_data.data[0].TABLE_ROWS + " rows";
                });
            }
        },
        execute_query() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/query.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database,
                                        "query": this.query })
            })
            .then(response => response.json())
            .then(json_data => {
                if (json_data.data !== undefined) {
                    this.query_elements = json_data.data;
                } else {
                    this.$emit("returnEvent", json_data);
                }
            })
        },
        preparing_row() {
            let building_obj = {};
            for (var i = 0; i < this.columns.length; ++i) {
                let obj = Object.assign(building_obj, 
                    {[this.columns[i]["Field"]]: (this.index_to_edit >= 0 ? this.elements[this.index_to_edit][this.columns[i]["Field"]] : "")});
                building_obj = obj;
            }
            return building_obj;
        },
        add_row() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/insert_into.php", {
                method: "POST",
                body: JSON.stringify({ "database_name" : this.selected_database,
                                        "table_name": this.selected_table, 
                                        "values": this.object_to_add })
            })
            .then(response => response.json())
            .then(json_data => {
                this.get_elements();
                this.$emit("returnEvent", json_data);
                this.adding_row = false;
                this.object_to_add = null;
            })
        },
        update_row() {
            fetch("http://rynnoceros.etnaformation.local/backend/tables/update_table.php", {
                method: "POST",
                body: JSON.stringify({ "database_name": this.selected_database,
                                        "table_name": this.selected_table,
                                        "criterias": this.elements[this.index_to_edit],
                                        "new_values": this.object_to_modify})
            })
            .then(response => response.json())
            .then(json_data => {
                this.get_elements();
                this.$emit("returnEvent", json_data);
                this.modifying_row = false;
                this.object_to_modify = null;
            })
        }
    },
    computed:  {
        current_database: function() {
            this.get_tables();
            this.get_elements();
            this.get_columns();
            return this.selected_database;
        },
    }
}