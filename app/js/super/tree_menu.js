
$(function(){
   
     function getNivelSelecionado() {
        return $("#nivel").val();
    }

    $("#tree").fancytree({
       source: {
            url: "tree_menu.php",
            type: "GET",
            dataType: "json",
            cache: false,

            data: {
                action: "get_tree",

                // ✔ jQuery EXECUTA esta function
                nivel: function () {
                    return $("#nivel").val();
                }
            }
        },
        extensions: ["dnd5","edit"],
        quicksearch: true,
        checkbox: false,
        icon: false,
        aria: true,
        escapeTitles: false,
        edit: {
            triggerStart: ["f2", "shift+click"],
            save: function(event, data){
                var node = data.node;
                var newTitle = data.input.val();
                $.post("tree_menu.php", {
                    action: "rename_node",
                    id: node.key,
                    title: newTitle,
                    icone: node.data.icone || "",
                    link: node.data.link || ""
                }, function(resp){
                    if (resp.status !== "ok") alert("Erro: "+(resp.message||""));
                    else node.setTitle(newTitle);
                }, "json");
            }
        },
        dnd5: {
            preventRecursion: true,
            dragStart: function(node, data){ return true; },
            dragEnter: function(node, data){ return true; },
            dragDrop: function(node, data){
                var src = data.otherNode;
                var target = node;
                var hitMode = data.hitMode;
                var newParent;
                var position = 0;

                if (hitMode === "over") {
                    newParent = target.key;
                    var tgtNode = $("#tree").fancytree("getTree").getNodeByKey(newParent);
                    position = tgtNode && tgtNode.getChildren() ? tgtNode.getChildren().length : 0;
                } else {
                    newParent = target.parent ? target.parent.key : "0";
                    var siblings = target.getParent().getChildren();
                    var idx = siblings.indexOf(target);
                    position = (hitMode === "before") ? idx : idx+1;
                }

                data.otherNode.moveTo(target, hitMode);

                $.post("tree_menu.php", {
                    action: "move_node",
                    id: src.key,
                    parent: (newParent === null ? 0 : newParent),
                    position: position
                }, function(resp){
                    if (resp.status !== "ok") {
                        alert("Erro ao mover: " + (resp.message || ""));
                        $("#tree").fancytree("getTree").reload();
                    }
                }, "json");
            }
        }
    });

    // Menu de contexto usando jquery-contextmenu
    $.contextMenu({
        selector: "#tree .fancytree-node",
        items: {
            "create": { name: "Novo (filho)" },
            "rename": { name: "Renomear" },
            "editicon": { name: "Editar ícone" },
            "editlink": { name: "Editar link" },
            "sep1": "---------",
            "delete": { name: "Excluir" }
        },
        callback: function(action, options) {
            var node = $.ui.fancytree.getNode(options.$trigger);
            if (!node) return;

            if (action === "create") {
                $.post("tree_menu.php", {
                    action: "create_node",
                    parent: node.key,
                    title: "Novo Item",
                    icone: "",
                    link: "",
                    nivel: $("#nivel").val()
                }, function(resp){
                    if (resp.status === "ok") {
                        var newId = resp.id;
                        node.addChildren({ title: "Novo Item", key: String(newId), data: { icone: "", link: "", nivel: $("#nivel").val() } });
                        node.setExpanded(true);
                        var child = node.getChildren().filter(function(n){ return n.key == newId; })[0];
                        if (child) child.editStart();
                    } else alert("Erro: " + (resp.message||""));
                }, "json");
            } else if (action === "rename") {
                node.editStart();
            } else if (action === "editicon") {
                currentEditNode = node;
                iconModal.show();
            } else if (action === "editlink") {
                var curLink = node.data.link || "";
                var novoLink = prompt("Link:", curLink);
                if (novoLink === null) return;

                $.post("tree_menu.php", {
                    action: "rename_node",
                    id: node.key,
                    title: node.title.replace(/<[^>]*>/g,'').trim(),
                    icone: node.data.icone || "",
                    link: novoLink
                }, function(resp){
                    if (resp.status === "ok") {
                        node.data.link = novoLink;
                    } else alert("Erro: " + (resp.message||""));
                }, "json");
            } else if (action === "delete") {
                if (!confirm("Excluir este nó e todos os seus filhos?")) return;
                $.post("tree_menu.php", {
                    action: "delete_node",
                    id: node.key
                }, function(resp){
                    if (resp.status === "ok") {
                        node.remove();
                    } else alert("Erro: " + (resp.message||""));
                }, "json");
            }
        }
    });

    $("#btnAddRoot").on("click", function(){
        $.post("tree_menu.php", { action: "create_node", parent: 0, title: "Novo Item", icone: "", link: "", nivel: $("#nivel").val() }, function(resp){
            if (resp.status === "ok") {
                var newId = resp.id;
                var tree = $("#tree").fancytree("getTree");
                tree.rootNode.addChildren({ title: "Novo Item", key: newId, data: { icone:"", link:"", nivel: $("#nivel").val() } });
                var node = tree.getNodeByKey(newId);
                if (node) node.editStart();
            } else alert("Erro: " + (resp.message||""));
        }, "json");
    });

    $("#nivel").on("change", function () {
        $("#tree").fancytree("getTree").reload();
    });

    $("#btnRefresh").on("click", function(){
        $("#tree").fancytree("getTree").reload();
    });

    $("#tree").on("dblclick", function(e){
        var node = $.ui.fancytree.getNode(e);
        if (node && node.data && node.data.link) {
            var url = node.data.link;
            if (url && url !== "#" && url !== "") window.open(url, "_blank");
        }
    });
	
});