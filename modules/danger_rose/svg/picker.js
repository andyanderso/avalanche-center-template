var color_idx = 0;
var colors = new Array("#999","#0f0","#ff3","#f93","#f00","#000", "#4db8f8");

var target_base;
var numcolumns;
var numelements;

// Initialize the rose array from the form element values and load the rose.
function init() {

  var solid_color;
  var dot_color;
  var element_color;

  var param = document.defaultView.frameElement.getElementsByTagName("param");

  // XXX: WebKit seems to be choking on namedItem(), possibly because of [] in name. Use item() instead.
  target_base = param.item(0).value;
  numcolumns = param.item(1).value;
  numelements = param.item(2).value;

  for (var i = 0; i < numelements; i++) {
    element_color = get_rose_element(i).value;
    solid_color = dot_color = Math.floor(element_color / 2);

    // If odd, it's a dot value and the next highest color rating
    if (element_color % 2 !== 0) {
      dot_color = solid_color + 1;
    }

    document.getElementById(i).setAttribute("fill", colors[solid_color]);
    document.getElementById("dot_" + i).setAttribute("fill", colors[dot_color]);
  }
}

// Returns the element at position i
function get_rose_element(i) {
  return window.parent.document.getElementsByName(target_base + '[' + i + ']')[0];
}

// Sets the value in the hidden textfield representing the element in the rose.
function set_rose_value(i, val) {
  get_rose_element(i).value = val;
}

// Sets the active color to paint with.
function set_color(i) {
  color_idx = i;
  document.getElementById("active_color").setAttribute("fill", colors[color_idx]);
}

// Called when clicking the aspect label.
function fill_aspect(a) {
  var len = a.length;
  for (var j = 0; j < len; j++) { fill("", a[j]); }
}

// Sets the color of the element.
function fill(ev, id) {
  var ids = [];

  // Alt key is held down, fill the elevation.
  if (ev.altKey) {
    var max = 7;
    if (id < 8) { max = 7; }
    else if (id < 16) { max = 15; }
    else { max = 23; }
    for (var i = 0; i < 8; i++) {
      ids[i] = max;
      max--;
    }
  } else {
    ids[0] = id;
  }

  var rose_value;
  var new_rose_value;
  var solid_color;
  var dot_color;
  var len = ids.length;
  for (var i = 0; i < len; i++) {
    id = ids[i];

    rose_value = parseInt(get_rose_element(id).value);
    new_rose_value = color_idx * 2;
    solid_color = dot_color = colors[color_idx];

    // Detect if they've clicked on the element again with the same active color.
    if (color_idx === Math.floor(rose_value / 2))  {
      
      // If the current value is even, add the dot.
      // Any dotted value is always odd.
      if (rose_value % 2 === 0) {
        new_rose_value = new_rose_value + 1;
        dot_color = colors[color_idx + 1];
      }
    }

    set_rose_value(id, new_rose_value);
    document.getElementById(id).setAttribute("fill", solid_color);
    document.getElementById("dot_"+ id).setAttribute("fill", dot_color);
  }    
}
