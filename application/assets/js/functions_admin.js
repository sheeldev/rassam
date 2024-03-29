(function() {}).call(this),
/*
* jQuery JavaScript Library v2.0.3
* http://jquery.com/
*
* Includes Sizzle.js
* http://sizzlejs.com/
*
* Copyright 2005, 2013 jQuery Foundation, Inc. and other contributors
* Released under the MIT license
* http://jquery.org/license
*/
(function(window, undefined) {
var
    rootjQuery,
    readyList,
    core_strundefined = typeof undefined,
    location = window.location,
    document = window.document,
    docElem = document.documentElement,
    _jQuery = window.jQuery,
    _$ = window.$,
    class2type = {},
    core_deletedIds = [],
    core_version = "2.0.3",
    core_concat = core_deletedIds.concat,
    core_push = core_deletedIds.push,
    core_slice = core_deletedIds.slice,
    core_indexOf = core_deletedIds.indexOf,
    core_toString = class2type.toString,
    core_hasOwn = class2type.hasOwnProperty,
    core_trim = core_version.trim,
    jQuery = function( selector, context ) {
            return new jQuery.fn.init( selector, context, rootjQuery );
    },
    core_pnum = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
    core_rnotwhite = /\S+/g,
    rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,
    rsingleTag = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
    rmsPrefix = /^-ms-/,
    rdashAlpha = /-([\da-z])/gi,
    fcamelCase = function( all, letter ) {
            return letter.toUpperCase();
    },
    completed = function() {
            document.removeEventListener( "DOMContentLoaded", completed, false );
            window.removeEventListener( "load", completed, false );
            jQuery.ready();
    };
jQuery.fn = jQuery.prototype = {
    // The current version of jQuery being used
    jquery: core_version,
    constructor: jQuery,
    init: function( selector, context, rootjQuery ) {
            var match, elem;
            if ( !selector ) {
                    return this;
            }
            // Handle HTML strings
            if ( typeof selector === "string" ) {
                    if ( selector.charAt(0) === "<" && selector.charAt( selector.length - 1 ) === ">" && selector.length >= 3 ) {
                            // Assume that strings that start and end with <> are HTML and skip the regex check
                            match = [ null, selector, null ];
                    } else {
                            match = rquickExpr.exec( selector );
                    }
                    if ( match && (match[1] || !context) ) {
                            if ( match[1] ) {
                                    context = context instanceof jQuery ? context[0] : context;
                                    // scripts is true for back-compat
                                    jQuery.merge( this, jQuery.parseHTML(
                                            match[1],
                                            context && context.nodeType ? context.ownerDocument || context : document,
                                            true
                                    ) );
                                    // HANDLE: $(html, props)
                                    if ( rsingleTag.test( match[1] ) && jQuery.isPlainObject( context ) ) {
                                            for ( match in context ) {
                                                    // Properties of context are called as methods if possible
                                                    if ( jQuery.isFunction( this[ match ] ) ) {
                                                            this[ match ]( context[ match ] );
                                                    // ...and otherwise set as attributes
                                                    } else {
                                                            this.attr( match, context[ match ] );
                                                    }
                                            }
                                    }
                                    return this;
                            } else {
                                    elem = document.getElementById( match[2] );
                                    // Check parentNode to catch when Blackberry 4.6 returns
                                    // nodes that are no longer in the document #6963
                                    if ( elem && elem.parentNode ) {
                                            // Inject the element directly into the jQuery object
                                            this.length = 1;
                                            this[0] = elem;
                                    }
                                    this.context = document;
                                    this.selector = selector;
                                    return this;
                            }
                    } else if ( !context || context.jquery ) {
                            return ( context || rootjQuery ).find( selector );
                    } else {
                            return this.constructor( context ).find( selector );
                    }
            } else if ( selector.nodeType ) {
                    this.context = this[0] = selector;
                    this.length = 1;
                    return this;
            } else if ( jQuery.isFunction( selector ) ) {
                    return rootjQuery.ready( selector );
            }
            if ( selector.selector !== undefined ) {
                    this.selector = selector.selector;
                    this.context = selector.context;
            }
            return jQuery.makeArray( selector, this );
    },

    // Start with an empty selector
    selector: "",
    // The default length of a jQuery object is 0
    length: 0,
    toArray: function() {
            return core_slice.call( this );
    },
    // Get the Nth element in the matched element set OR
    // Get the whole matched element set as a clean array
    get: function( num ) {
            return num == null ?
                    // Return a 'clean' array
                    this.toArray() :
                    // Return just the object
                    ( num < 0 ? this[ this.length + num ] : this[ num ] );
    },
    // Take an array of elements and push it onto the stack
    // (returning the new matched element set)
    pushStack: function( elems ) {
            // Build a new jQuery matched element set
            var ret = jQuery.merge( this.constructor(), elems );
            // Add the old object onto the stack (as a reference)
            ret.prevObject = this;
            ret.context = this.context;
            // Return the newly-formed element set
            return ret;
    },
    // Execute a callback for every element in the matched set.
    // (You can seed the arguments with an array of args, but this is
    // only used internally.)
    each: function( callback, args ) {
            return jQuery.each( this, callback, args );
    },
    ready: function( fn ) {
            // Add the callback
            jQuery.ready.promise().done( fn );
            return this;
    },
    slice: function() {
            return this.pushStack( core_slice.apply( this, arguments ) );
    },
    first: function() {
            return this.eq( 0 );
    },
    last: function() {
            return this.eq( -1 );
    },
    eq: function( i ) {
            var len = this.length,
                    j = +i + ( i < 0 ? len : 0 );
            return this.pushStack( j >= 0 && j < len ? [ this[j] ] : [] );
    },
    map: function( callback ) {
            return this.pushStack( jQuery.map(this, function( elem, i ) {
                    return callback.call( elem, i, elem );
            }));
    },
    end: function() {
            return this.prevObject || this.constructor(null);
    },
    // For internal use only.
    // Behaves like an Array's method, not like a jQuery method.
    push: core_push,
    sort: [].sort,
    splice: [].splice
};
// Give the init function the jQuery prototype for later instantiation
jQuery.fn.init.prototype = jQuery.fn;
jQuery.extend = jQuery.fn.extend = function() {
    var options, name, src, copy, copyIsArray, clone,
            target = arguments[0] || {},
            i = 1,
            length = arguments.length,
            deep = false;

    if ( typeof target === "boolean" ) {
            deep = target;
            target = arguments[1] || {};
            // skip the boolean and the target
            i = 2;
    }

    if ( typeof target !== "object" && !jQuery.isFunction(target) ) {
            target = {};
    }

    if ( length === i ) {
            target = this;
            --i;
    }
    for ( ; i < length; i++ ) {
            // Only deal with non-null/undefined values
            if ( (options = arguments[ i ]) != null ) {
                    // Extend the base object
                    for ( name in options ) {
                            src = target[ name ];
                            copy = options[ name ];

                            // Prevent never-ending loop
                            if ( target === copy ) {
                                    continue;
                            }
                            // Recurse if we're merging plain objects or arrays
                            if ( deep && copy && ( jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)) ) ) {
                                    if ( copyIsArray ) {
                                            copyIsArray = false;
                                            clone = src && jQuery.isArray(src) ? src : [];

                                    } else {
                                            clone = src && jQuery.isPlainObject(src) ? src : {};
                                    }
                                    // Never move original objects, clone them
                                    target[ name ] = jQuery.extend( deep, clone, copy );
                            // Don't bring in undefined values
                            } else if ( copy !== undefined ) {
                                    target[ name ] = copy;
                            }
                    }
            }
    }
    return target;
};
jQuery.extend({
    expando: "jQuery" + ( core_version + Math.random() ).replace( /\D/g, "" ),
    noConflict: function( deep ) {
            if ( window.$ === jQuery ) {
                    window.$ = _$;
            }

            if ( deep && window.jQuery === jQuery ) {
                    window.jQuery = _jQuery;
            }

            return jQuery;
    },
    // Is the DOM ready to be used? Set to true once it occurs.
    isReady: false,
    // A counter to track how many items to wait for before
    // the ready event fires. See #6781
    readyWait: 1,
    // Hold (or release) the ready event
    holdReady: function( hold ) {
            if ( hold ) {
                    jQuery.readyWait++;
            } else {
                    jQuery.ready( true );
            }
    },
    // Handle when the DOM is ready
    ready: function( wait ) {
            // Abort if there are pending holds or we're already ready
            if ( wait === true ? --jQuery.readyWait : jQuery.isReady ) {
                    return;
            }
            // Remember that the DOM is ready
            jQuery.isReady = true;
            // If a normal DOM Ready event fired, decrement, and wait if need be
            if ( wait !== true && --jQuery.readyWait > 0 ) {
                    return;
            }
            // If there are functions bound, to execute
            readyList.resolveWith( document, [ jQuery ] );
            // Trigger any bound ready events
            if ( jQuery.fn.trigger ) {
                    jQuery( document ).trigger("ready").off("ready");
            }
    },
    // See test/unit/core.js for details concerning isFunction.
    // Since version 1.3, DOM methods and functions like alert
    // aren't supported. They return false on IE (#2968).
    isFunction: function( obj ) {
            return jQuery.type(obj) === "function";
    },
    isArray: Array.isArray,
    isWindow: function( obj ) {
            return obj != null && obj === obj.window;
    },
    isNumeric: function( obj ) {
            return !isNaN( parseFloat(obj) ) && isFinite( obj );
    },
    type: function( obj ) {
            if ( obj == null ) {
                    return String( obj );
            }
            // Support: Safari <= 5.1 (functionish RegExp)
            return typeof obj === "object" || typeof obj === "function" ?
                    class2type[ core_toString.call(obj) ] || "object" :
                    typeof obj;
    },
    isPlainObject: function( obj ) {
            // Not plain objects:
            // - Any object or value whose internal [[Class]] property is not "[object Object]"
            // - DOM nodes
            // - window
            if ( jQuery.type( obj ) !== "object" || obj.nodeType || jQuery.isWindow( obj ) ) {
                    return false;
            }

            // Support: Firefox <20
            // The try/catch suppresses exceptions thrown when attempting to access
            // the "constructor" property of certain host objects, ie. |window.location|
            // https://bugzilla.mozilla.org/show_bug.cgi?id=814622
            try {
                    if ( obj.constructor &&
                                    !core_hasOwn.call( obj.constructor.prototype, "isPrototypeOf" ) ) {
                            return false;
                    }
            } catch ( e ) {
                    return false;
            }

            // If the function hasn't returned already, we're confident that
            // |obj| is a plain object, created by {} or constructed with new Object
            return true;
    },
    isEmptyObject: function( obj ) {
            var name;
            for ( name in obj ) {
                    return false;
            }
            return true;
    },
    error: function( msg ) {
            throw new Error( msg );
    },
    // data: string of html
    // context (optional): If specified, the fragment will be created in this context, defaults to document
    // keepScripts (optional): If true, will include scripts passed in the html string
    parseHTML: function( data, context, keepScripts ) {
            if ( !data || typeof data !== "string" ) {
                    return null;
            }
            if ( typeof context === "boolean" ) {
                    keepScripts = context;
                    context = false;
            }
            context = context || document;

            var parsed = rsingleTag.exec( data ),
                    scripts = !keepScripts && [];

            // Single tag
            if ( parsed ) {
                    return [ context.createElement( parsed[1] ) ];
            }

            parsed = jQuery.buildFragment( [ data ], context, scripts );

            if ( scripts ) {
                    jQuery( scripts ).remove();
            }

            return jQuery.merge( [], parsed.childNodes );
    },

    parseJSON: JSON.parse,

    // Cross-browser xml parsing
    parseXML: function( data ) {
            var xml, tmp;
            if ( !data || typeof data !== "string" ) {
                    return null;
            }

            // Support: IE9
            try {
                    tmp = new DOMParser();
                    xml = tmp.parseFromString( data , "text/xml" );
            } catch ( e ) {
                    xml = undefined;
            }

            if ( !xml || xml.getElementsByTagName( "parsererror" ).length ) {
                    jQuery.error( "Invalid XML: " + data );
            }
            return xml;
    },

    noop: function() {},

    // Evaluates a script in a global context
    globalEval: function( code ) {
            var script,
                            indirect = eval;

            code = jQuery.trim( code );

            if ( code ) {
                    // If the code includes a valid, prologue position
                    // strict mode pragma, execute code by injecting a
                    // script tag into the document.
                    if ( code.indexOf("use strict") === 1 ) {
                            script = document.createElement("script");
                            script.text = code;
                            document.head.appendChild( script ).parentNode.removeChild( script );
                    } else {
                    // Otherwise, avoid the DOM node creation, insertion
                    // and removal by using an indirect global eval
                            indirect( code );
                    }
            }
    },

    // Convert dashed to camelCase; used by the css and data modules
    // Microsoft forgot to hump their vendor prefix (#9572)
    camelCase: function( string ) {
            return string.replace( rmsPrefix, "ms-" ).replace( rdashAlpha, fcamelCase );
    },

    nodeName: function( elem, name ) {
            return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
    },

    // args is for internal usage only
    each: function( obj, callback, args ) {
            var value,
                    i = 0,
                    length = obj.length,
                    isArray = isArraylike( obj );

            if ( args ) {
                    if ( isArray ) {
                            for ( ; i < length; i++ ) {
                                    value = callback.apply( obj[ i ], args );

                                    if ( value === false ) {
                                            break;
                                    }
                            }
                    } else {
                            for ( i in obj ) {
                                    value = callback.apply( obj[ i ], args );

                                    if ( value === false ) {
                                            break;
                                    }
                            }
                    }

            // A special, fast, case for the most common use of each
            } else {
                    if ( isArray ) {
                            for ( ; i < length; i++ ) {
                                    value = callback.call( obj[ i ], i, obj[ i ] );

                                    if ( value === false ) {
                                            break;
                                    }
                            }
                    } else {
                            for ( i in obj ) {
                                    value = callback.call( obj[ i ], i, obj[ i ] );

                                    if ( value === false ) {
                                            break;
                                    }
                            }
                    }
            }

            return obj;
    },

    trim: function( text ) {
            return text == null ? "" : core_trim.call( text );
    },

    // results is for internal usage only
    makeArray: function( arr, results ) {
            var ret = results || [];

            if ( arr != null ) {
                    if ( isArraylike( Object(arr) ) ) {
                            jQuery.merge( ret,
                                    typeof arr === "string" ?
                                    [ arr ] : arr
                            );
                    } else {
                            core_push.call( ret, arr );
                    }
            }

            return ret;
    },

    inArray: function( elem, arr, i ) {
            return arr == null ? -1 : core_indexOf.call( arr, elem, i );
    },

    merge: function( first, second ) {
            var l = second.length,
                    i = first.length,
                    j = 0;

            if ( typeof l === "number" ) {
                    for ( ; j < l; j++ ) {
                            first[ i++ ] = second[ j ];
                    }
            } else {
                    while ( second[j] !== undefined ) {
                            first[ i++ ] = second[ j++ ];
                    }
            }

            first.length = i;

            return first;
    },

    grep: function( elems, callback, inv ) {
            var retVal,
                    ret = [],
                    i = 0,
                    length = elems.length;
            inv = !!inv;

            // Go through the array, only saving the items
            // that pass the validator function
            for ( ; i < length; i++ ) {
                    retVal = !!callback( elems[ i ], i );
                    if ( inv !== retVal ) {
                            ret.push( elems[ i ] );
                    }
            }

            return ret;
    },

    // arg is for internal usage only
    map: function( elems, callback, arg ) {
            var value,
                    i = 0,
                    length = elems.length,
                    isArray = isArraylike( elems ),
                    ret = [];

            // Go through the array, translating each of the items to their
            if ( isArray ) {
                    for ( ; i < length; i++ ) {
                            value = callback( elems[ i ], i, arg );

                            if ( value != null ) {
                                    ret[ ret.length ] = value;
                            }
                    }

            // Go through every key on the object,
            } else {
                    for ( i in elems ) {
                            value = callback( elems[ i ], i, arg );

                            if ( value != null ) {
                                    ret[ ret.length ] = value;
                            }
                    }
            }

            // Flatten any nested arrays
            return core_concat.apply( [], ret );
    },

    // A global GUID counter for objects
    guid: 1,

    // Bind a function to a context, optionally partially applying any
    // arguments.
    proxy: function( fn, context ) {
            var tmp, args, proxy;

            if ( typeof context === "string" ) {
                    tmp = fn[ context ];
                    context = fn;
                    fn = tmp;
            }

            // Quick check to determine if target is callable, in the spec
            // this throws a TypeError, but we will just return undefined.
            if ( !jQuery.isFunction( fn ) ) {
                    return undefined;
            }

            // Simulated bind
            args = core_slice.call( arguments, 2 );
            proxy = function() {
                    return fn.apply( context || this, args.concat( core_slice.call( arguments ) ) );
            };

            // Set the guid of unique handler to the same of original handler, so it can be removed
            proxy.guid = fn.guid = fn.guid || jQuery.guid++;

            return proxy;
    },

    // Multifunctional method to get and set values of a collection
    // The value/s can optionally be executed if it's a function
    access: function( elems, fn, key, value, chainable, emptyGet, raw ) {
            var i = 0,
                    length = elems.length,
                    bulk = key == null;

            // Sets many values
            if ( jQuery.type( key ) === "object" ) {
                    chainable = true;
                    for ( i in key ) {
                            jQuery.access( elems, fn, i, key[i], true, emptyGet, raw );
                    }

            // Sets one value
            } else if ( value !== undefined ) {
                    chainable = true;

                    if ( !jQuery.isFunction( value ) ) {
                            raw = true;
                    }

                    if ( bulk ) {
                            // Bulk operations run against the entire set
                            if ( raw ) {
                                    fn.call( elems, value );
                                    fn = null;

                            // ...except when executing function values
                            } else {
                                    bulk = fn;
                                    fn = function( elem, key, value ) {
                                            return bulk.call( jQuery( elem ), value );
                                    };
                            }
                    }

                    if ( fn ) {
                            for ( ; i < length; i++ ) {
                                    fn( elems[i], key, raw ? value : value.call( elems[i], i, fn( elems[i], key ) ) );
                            }
                    }
            }

            return chainable ?
                    elems :

                    // Gets
                    bulk ?
                            fn.call( elems ) :
                            length ? fn( elems[0], key ) : emptyGet;
    },

    now: Date.now,

    // A method for quickly swapping in/out CSS properties to get correct calculations.
    // Note: this method belongs to the css module but it's needed here for the support module.
    // If support gets modularized, this method should be moved back to the css module.
    swap: function( elem, options, callback, args ) {
            var ret, name,
                    old = {};

            // Remember the old values, and insert the new ones
            for ( name in options ) {
                    old[ name ] = elem.style[ name ];
                    elem.style[ name ] = options[ name ];
            }

            ret = callback.apply( elem, args || [] );

            // Revert the old values
            for ( name in options ) {
                    elem.style[ name ] = old[ name ];
            }

            return ret;
    }
});

jQuery.ready.promise = function( obj ) {
    if ( !readyList ) {

            readyList = jQuery.Deferred();

            // Catch cases where $(document).ready() is called after the browser event has already occurred.
            // we once tried to use readyState "interactive" here, but it caused issues like the one
            // discovered by ChrisS here: http://bugs.jquery.com/ticket/12282#comment:15
            if ( document.readyState === "complete" ) {
                    // Handle it asynchronously to allow scripts the opportunity to delay ready
                    setTimeout( jQuery.ready );

            } else {

                    // Use the handy event callback
                    document.addEventListener( "DOMContentLoaded", completed, false );

                    // A fallback to window.onload, that will always work
                    window.addEventListener( "load", completed, false );
            }
    }
    return readyList.promise( obj );
};

// Populate the class2type map
jQuery.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function(i, name) {
    class2type[ "[object " + name + "]" ] = name.toLowerCase();
});

function isArraylike( obj ) {
    var length = obj.length,
            type = jQuery.type( obj );

    if ( jQuery.isWindow( obj ) ) {
            return false;
    }

    if ( obj.nodeType === 1 && length ) {
            return true;
    }

    return type === "array" || type !== "function" &&
            ( length === 0 ||
            typeof length === "number" && length > 0 && ( length - 1 ) in obj );
}

// All jQuery objects should point back to these
rootjQuery = jQuery(document);
/*
* Sizzle CSS Selector Engine v1.9.4-pre
* http://sizzlejs.com/
*
* Copyright 2013 jQuery Foundation, Inc. and other contributors
* Released under the MIT license
* http://jquery.org/license
*
* Date: 2013-06-03
*/
(function( window, undefined ) {

var i,
    support,
    cachedruns,
    Expr,
    getText,
    isXML,
    compile,
    outermostContext,
    sortInput,

    // Local document vars
    setDocument,
    document,
    docElem,
    documentIsHTML,
    rbuggyQSA,
    rbuggyMatches,
    matches,
    contains,

    // Instance-specific data
    expando = "sizzle" + -(new Date()),
    preferredDoc = window.document,
    dirruns = 0,
    done = 0,
    classCache = createCache(),
    tokenCache = createCache(),
    compilerCache = createCache(),
    hasDuplicate = false,
    sortOrder = function( a, b ) {
            if ( a === b ) {
                    hasDuplicate = true;
                    return 0;
            }
            return 0;
    },

    // General-purpose constants
    strundefined = typeof undefined,
    MAX_NEGATIVE = 1 << 31,

    // Instance methods
    hasOwn = ({}).hasOwnProperty,
    arr = [],
    pop = arr.pop,
    push_native = arr.push,
    push = arr.push,
    slice = arr.slice,
    // Use a stripped-down indexOf if we can't use a native one
    indexOf = arr.indexOf || function( elem ) {
            var i = 0,
                    len = this.length;
            for ( ; i < len; i++ ) {
                    if ( this[i] === elem ) {
                            return i;
                    }
            }
            return -1;
    },

    booleans = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",

    // Regular expressions

    // Whitespace characters http://www.w3.org/TR/css3-selectors/#whitespace
    whitespace = "[\\x20\\t\\r\\n\\f]",
    // http://www.w3.org/TR/css3-syntax/#characters
    characterEncoding = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",

    // Loosely modeled on CSS identifier characters
    // An unquoted value should be a CSS identifier http://www.w3.org/TR/css3-selectors/#attribute-selectors
    // Proper syntax: http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
    identifier = characterEncoding.replace( "w", "w#" ),

    // Acceptable operators http://www.w3.org/TR/selectors/#attribute-selectors
    attributes = "\\[" + whitespace + "*(" + characterEncoding + ")" + whitespace +
            "*(?:([*^$|!~]?=)" + whitespace + "*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|(" + identifier + ")|)|)" + whitespace + "*\\]",

    // Prefer arguments quoted,
    //   then not containing pseudos/brackets,
    //   then attribute selectors/non-parenthetical expressions,
    //   then anything else
    // These preferences are here to reduce the number of selectors
    //   needing tokenize in the PSEUDO preFilter
    pseudos = ":(" + characterEncoding + ")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|" + attributes.replace( 3, 8 ) + ")*)|.*)\\)|)",

    // Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
    rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" ),

    rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" ),
    rcombinators = new RegExp( "^" + whitespace + "*([>+~]|" + whitespace + ")" + whitespace + "*" ),

    rsibling = new RegExp( whitespace + "*[+~]" ),
    rattributeQuotes = new RegExp( "=" + whitespace + "*([^\\]'\"]*)" + whitespace + "*\\]", "g" ),

    rpseudo = new RegExp( pseudos ),
    ridentifier = new RegExp( "^" + identifier + "$" ),

    matchExpr = {
            "ID": new RegExp( "^#(" + characterEncoding + ")" ),
            "CLASS": new RegExp( "^\\.(" + characterEncoding + ")" ),
            "TAG": new RegExp( "^(" + characterEncoding.replace( "w", "w*" ) + ")" ),
            "ATTR": new RegExp( "^" + attributes ),
            "PSEUDO": new RegExp( "^" + pseudos ),
            "CHILD": new RegExp( "^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + whitespace +
                    "*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace +
                    "*(\\d+)|))" + whitespace + "*\\)|)", "i" ),
            "bool": new RegExp( "^(?:" + booleans + ")$", "i" ),
            // For use in libraries implementing .is()
            // We use this for POS matching in `select`
            "needsContext": new RegExp( "^" + whitespace + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" +
                    whitespace + "*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)", "i" )
    },

    rnative = /^[^{]+\{\s*\[native \w/,

    // Easily-parseable/retrievable ID or TAG or CLASS selectors
    rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,

    rinputs = /^(?:input|select|textarea|button)$/i,
    rheader = /^h\d$/i,

    rescape = /'|\\/g,

    // CSS escapes http://www.w3.org/TR/CSS21/syndata.html#escaped-characters
    runescape = new RegExp( "\\\\([\\da-f]{1,6}" + whitespace + "?|(" + whitespace + ")|.)", "ig" ),
    funescape = function( _, escaped, escapedWhitespace ) {
            var high = "0x" + escaped - 0x10000;
            // NaN means non-codepoint
            // Support: Firefox
            // Workaround erroneous numeric interpretation of +"0x"
            return high !== high || escapedWhitespace ?
                    escaped :
                    // BMP codepoint
                    high < 0 ?
                            String.fromCharCode( high + 0x10000 ) :
                            // Supplemental Plane codepoint (surrogate pair)
                            String.fromCharCode( high >> 10 | 0xD800, high & 0x3FF | 0xDC00 );
    };

// Optimize for push.apply( _, NodeList )
try {
    push.apply(
            (arr = slice.call( preferredDoc.childNodes )),
            preferredDoc.childNodes
    );
    // Support: Android<4.0
    // Detect silently failing push.apply
    arr[ preferredDoc.childNodes.length ].nodeType;
} catch ( e ) {
    push = { apply: arr.length ?

            // Leverage slice if possible
            function( target, els ) {
                    push_native.apply( target, slice.call(els) );
            } :

            // Support: IE<9
            // Otherwise append directly
            function( target, els ) {
                    var j = target.length,
                            i = 0;
                    // Can't trust NodeList.length
                    while ( (target[j++] = els[i++]) ) {}
                    target.length = j - 1;
            }
    };
}

function Sizzle( selector, context, results, seed ) {
    var match, elem, m, nodeType,
            // QSA vars
            i, groups, old, nid, newContext, newSelector;

    if ( ( context ? context.ownerDocument || context : preferredDoc ) !== document ) {
            setDocument( context );
    }

    context = context || document;
    results = results || [];

    if ( !selector || typeof selector !== "string" ) {
            return results;
    }

    if ( (nodeType = context.nodeType) !== 1 && nodeType !== 9 ) {
            return [];
    }

    if ( documentIsHTML && !seed ) {

            // Shortcuts
            if ( (match = rquickExpr.exec( selector )) ) {
                    // Speed-up: Sizzle("#ID")
                    if ( (m = match[1]) ) {
                            if ( nodeType === 9 ) {
                                    elem = context.getElementById( m );
                                    // Check parentNode to catch when Blackberry 4.6 returns
                                    // nodes that are no longer in the document #6963
                                    if ( elem && elem.parentNode ) {
                                            // Handle the case where IE, Opera, and Webkit return items
                                            // by name instead of ID
                                            if ( elem.id === m ) {
                                                    results.push( elem );
                                                    return results;
                                            }
                                    } else {
                                            return results;
                                    }
                            } else {
                                    // Context is not a document
                                    if ( context.ownerDocument && (elem = context.ownerDocument.getElementById( m )) &&
                                            contains( context, elem ) && elem.id === m ) {
                                            results.push( elem );
                                            return results;
                                    }
                            }

                    // Speed-up: Sizzle("TAG")
                    } else if ( match[2] ) {
                            push.apply( results, context.getElementsByTagName( selector ) );
                            return results;

                    // Speed-up: Sizzle(".CLASS")
                    } else if ( (m = match[3]) && support.getElementsByClassName && context.getElementsByClassName ) {
                            push.apply( results, context.getElementsByClassName( m ) );
                            return results;
                    }
            }

            // QSA path
            if ( support.qsa && (!rbuggyQSA || !rbuggyQSA.test( selector )) ) {
                    nid = old = expando;
                    newContext = context;
                    newSelector = nodeType === 9 && selector;

                    // qSA works strangely on Element-rooted queries
                    // We can work around this by specifying an extra ID on the root
                    // and working up from there (Thanks to Andrew Dupont for the technique)
                    // IE 8 doesn't work on object elements
                    if ( nodeType === 1 && context.nodeName.toLowerCase() !== "object" ) {
                            groups = tokenize( selector );

                            if ( (old = context.getAttribute("id")) ) {
                                    nid = old.replace( rescape, "\\$&" );
                            } else {
                                    context.setAttribute( "id", nid );
                            }
                            nid = "[id='" + nid + "'] ";

                            i = groups.length;
                            while ( i-- ) {
                                    groups[i] = nid + toSelector( groups[i] );
                            }
                            newContext = rsibling.test( selector ) && context.parentNode || context;
                            newSelector = groups.join(",");
                    }

                    if ( newSelector ) {
                            try {
                                    push.apply( results,
                                            newContext.querySelectorAll( newSelector )
                                    );
                                    return results;
                            } catch(qsaError) {
                            } finally {
                                    if ( !old ) {
                                            context.removeAttribute("id");
                                    }
                            }
                    }
            }
    }

    // All others
    return select( selector.replace( rtrim, "$1" ), context, results, seed );
}

function createCache() {
    var keys = [];
    function cache( key, value ) {
            if ( keys.push( key += " " ) > Expr.cacheLength ) {
                    delete cache[ keys.shift() ];
            }
            return (cache[ key ] = value);
    }
    return cache;
}

function markFunction( fn ) {
    fn[ expando ] = true;
    return fn;
}

function assert( fn ) {
    var div = document.createElement("div");
    try {
            return !!fn( div );
    } catch (e) {
            return false;
    } finally {
            // Remove from its parent by default
            if ( div.parentNode ) {
                    div.parentNode.removeChild( div );
            }
            // release memory in IE
            div = null;
    }
}

function addHandle( attrs, handler ) {
    var arr = attrs.split("|"),
            i = attrs.length;

    while ( i-- ) {
            Expr.attrHandle[ arr[i] ] = handler;
    }
}

/**
* Checks document order of two siblings
* @param {Element} a
* @param {Element} b
* @returns {Number} Returns less than 0 if a precedes b, greater than 0 if a follows b
*/
function siblingCheck( a, b ) {
    var cur = b && a,
            diff = cur && a.nodeType === 1 && b.nodeType === 1 &&
                    ( ~b.sourceIndex || MAX_NEGATIVE ) -
                    ( ~a.sourceIndex || MAX_NEGATIVE );

    // Use IE sourceIndex if available on both nodes
    if ( diff ) {
            return diff;
    }

    // Check if b follows a
    if ( cur ) {
            while ( (cur = cur.nextSibling) ) {
                    if ( cur === b ) {
                            return -1;
                    }
            }
    }

    return a ? 1 : -1;
}

/**
* Returns a function to use in pseudos for input types
* @param {String} type
*/
function createInputPseudo( type ) {
    return function( elem ) {
            var name = elem.nodeName.toLowerCase();
            return name === "input" && elem.type === type;
    };
}

/**
* Returns a function to use in pseudos for buttons
* @param {String} type
*/
function createButtonPseudo( type ) {
    return function( elem ) {
            var name = elem.nodeName.toLowerCase();
            return (name === "input" || name === "button") && elem.type === type;
    };
}

/**
* Returns a function to use in pseudos for positionals
* @param {Function} fn
*/
function createPositionalPseudo( fn ) {
    return markFunction(function( argument ) {
            argument = +argument;
            return markFunction(function( seed, matches ) {
                    var j,
                            matchIndexes = fn( [], seed.length, argument ),
                            i = matchIndexes.length;

                    // Match elements found at the specified indexes
                    while ( i-- ) {
                            if ( seed[ (j = matchIndexes[i]) ] ) {
                                    seed[j] = !(matches[j] = seed[j]);
                            }
                    }
            });
    });
}

/**
* Detect xml
* @param {Element|Object} elem An element or a document
*/
isXML = Sizzle.isXML = function( elem ) {
    // documentElement is verified for cases where it doesn't yet exist
    // (such as loading iframes in IE - #4833)
    var documentElement = elem && (elem.ownerDocument || elem).documentElement;
    return documentElement ? documentElement.nodeName !== "HTML" : false;
};

// Expose support vars for convenience
support = Sizzle.support = {};

/**
* Sets document-related variables once based on the current document
* @param {Element|Object} [doc] An element or document object to use to set the document
* @returns {Object} Returns the current document
*/
setDocument = Sizzle.setDocument = function( node ) {
    var doc = node ? node.ownerDocument || node : preferredDoc,
            parent = doc.defaultView;

    // If no document and documentElement is available, return
    if ( doc === document || doc.nodeType !== 9 || !doc.documentElement ) {
            return document;
    }

    // Set our document
    document = doc;
    docElem = doc.documentElement;

    // Support tests
    documentIsHTML = !isXML( doc );

    // Support: IE>8
    // If iframe document is assigned to "document" variable and if iframe has been reloaded,
    // IE will throw "permission denied" error when accessing "document" variable, see jQuery #13936
    // IE6-8 do not support the defaultView property so parent will be undefined
    if ( parent && parent.attachEvent && parent !== parent.top ) {
            parent.attachEvent( "onbeforeunload", function() {
                    setDocument();
            });
    }

    /* Attributes
    ---------------------------------------------------------------------- */

    // Support: IE<8
    // Verify that getAttribute really returns attributes and not properties (excepting IE8 booleans)
    support.attributes = assert(function( div ) {
            div.className = "i";
            return !div.getAttribute("className");
    });

    /* getElement(s)By*
    ---------------------------------------------------------------------- */

    // Check if getElementsByTagName("*") returns only elements
    support.getElementsByTagName = assert(function( div ) {
            div.appendChild( doc.createComment("") );
            return !div.getElementsByTagName("*").length;
    });

    // Check if getElementsByClassName can be trusted
    support.getElementsByClassName = assert(function( div ) {
            div.innerHTML = "<div class='a'></div><div class='a i'></div>";

            // Support: Safari<4
            // Catch class over-caching
            div.firstChild.className = "i";
            // Support: Opera<10
            // Catch gEBCN failure to find non-leading classes
            return div.getElementsByClassName("i").length === 2;
    });

    // Support: IE<10
    // Check if getElementById returns elements by name
    // The broken getElementById methods don't pick up programatically-set names,
    // so use a roundabout getElementsByName test
    support.getById = assert(function( div ) {
            docElem.appendChild( div ).id = expando;
            return !doc.getElementsByName || !doc.getElementsByName( expando ).length;
    });

    // ID find and filter
    if ( support.getById ) {
            Expr.find["ID"] = function( id, context ) {
                    if ( typeof context.getElementById !== strundefined && documentIsHTML ) {
                            var m = context.getElementById( id );
                            // Check parentNode to catch when Blackberry 4.6 returns
                            // nodes that are no longer in the document #6963
                            return m && m.parentNode ? [m] : [];
                    }
            };
            Expr.filter["ID"] = function( id ) {
                    var attrId = id.replace( runescape, funescape );
                    return function( elem ) {
                            return elem.getAttribute("id") === attrId;
                    };
            };
    } else {
            // Support: IE6/7
            // getElementById is not reliable as a find shortcut
            delete Expr.find["ID"];

            Expr.filter["ID"] =  function( id ) {
                    var attrId = id.replace( runescape, funescape );
                    return function( elem ) {
                            var node = typeof elem.getAttributeNode !== strundefined && elem.getAttributeNode("id");
                            return node && node.value === attrId;
                    };
            };
    }

    // Tag
    Expr.find["TAG"] = support.getElementsByTagName ?
            function( tag, context ) {
                    if ( typeof context.getElementsByTagName !== strundefined ) {
                            return context.getElementsByTagName( tag );
                    }
            } :
            function( tag, context ) {
                    var elem,
                            tmp = [],
                            i = 0,
                            results = context.getElementsByTagName( tag );

                    // Filter out possible comments
                    if ( tag === "*" ) {
                            while ( (elem = results[i++]) ) {
                                    if ( elem.nodeType === 1 ) {
                                            tmp.push( elem );
                                    }
                            }

                            return tmp;
                    }
                    return results;
            };

    // Class
    Expr.find["CLASS"] = support.getElementsByClassName && function( className, context ) {
            if ( typeof context.getElementsByClassName !== strundefined && documentIsHTML ) {
                    return context.getElementsByClassName( className );
            }
    };

    /* QSA/matchesSelector
    ---------------------------------------------------------------------- */

    // QSA and matchesSelector support

    // matchesSelector(:active) reports false when true (IE9/Opera 11.5)
    rbuggyMatches = [];

    // qSa(:focus) reports false when true (Chrome 21)
    // We allow this because of a bug in IE8/9 that throws an error
    // whenever `document.activeElement` is accessed on an iframe
    // So, we allow :focus to pass through QSA all the time to avoid the IE error
    // See http://bugs.jquery.com/ticket/13378
    rbuggyQSA = [];

    if ( (support.qsa = rnative.test( doc.querySelectorAll )) ) {
            // Build QSA regex
            // Regex strategy adopted from Diego Perini
            assert(function( div ) {
                    // Select is set to empty string on purpose
                    // This is to test IE's treatment of not explicitly
                    // setting a boolean content attribute,
                    // since its presence should be enough
                    // http://bugs.jquery.com/ticket/12359
                    div.innerHTML = "<select><option selected=''></option></select>";

                    // Support: IE8
                    // Boolean attributes and "value" are not treated correctly
                    if ( !div.querySelectorAll("[selected]").length ) {
                            rbuggyQSA.push( "\\[" + whitespace + "*(?:value|" + booleans + ")" );
                    }

                    // Webkit/Opera - :checked should return selected option elements
                    // http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
                    // IE8 throws error here and will not see later tests
                    if ( !div.querySelectorAll(":checked").length ) {
                            rbuggyQSA.push(":checked");
                    }
            });

            assert(function( div ) {

                    // Support: Opera 10-12/IE8
                    // ^= $= *= and empty values
                    // Should not select anything
                    // Support: Windows 8 Native Apps
                    // The type attribute is restricted during .innerHTML assignment
                    var input = doc.createElement("input");
                    input.setAttribute( "type", "hidden" );
                    div.appendChild( input ).setAttribute( "t", "" );

                    if ( div.querySelectorAll("[t^='']").length ) {
                            rbuggyQSA.push( "[*^$]=" + whitespace + "*(?:''|\"\")" );
                    }

                    // FF 3.5 - :enabled/:disabled and hidden elements (hidden elements are still enabled)
                    // IE8 throws error here and will not see later tests
                    if ( !div.querySelectorAll(":enabled").length ) {
                            rbuggyQSA.push( ":enabled", ":disabled" );
                    }

                    // Opera 10-11 does not throw on post-comma invalid pseudos
                    div.querySelectorAll("*,:x");
                    rbuggyQSA.push(",.*:");
            });
    }

    if ( (support.matchesSelector = rnative.test( (matches = docElem.webkitMatchesSelector ||
            docElem.mozMatchesSelector ||
            docElem.oMatchesSelector ||
            docElem.msMatchesSelector) )) ) {

            assert(function( div ) {
                    // Check to see if it's possible to do matchesSelector
                    // on a disconnected node (IE 9)
                    support.disconnectedMatch = matches.call( div, "div" );

                    // This should fail with an exception
                    // Gecko does not error, returns false instead
                    matches.call( div, "[s!='']:x" );
                    rbuggyMatches.push( "!=", pseudos );
            });
    }

    rbuggyQSA = rbuggyQSA.length && new RegExp( rbuggyQSA.join("|") );
    rbuggyMatches = rbuggyMatches.length && new RegExp( rbuggyMatches.join("|") );

    /* Contains
    ---------------------------------------------------------------------- */

    // Element contains another
    // Purposefully does not implement inclusive descendent
    // As in, an element does not contain itself
    contains = rnative.test( docElem.contains ) || docElem.compareDocumentPosition ?
            function( a, b ) {
                    var adown = a.nodeType === 9 ? a.documentElement : a,
                            bup = b && b.parentNode;
                    return a === bup || !!( bup && bup.nodeType === 1 && (
                            adown.contains ?
                                    adown.contains( bup ) :
                                    a.compareDocumentPosition && a.compareDocumentPosition( bup ) & 16
                    ));
            } :
            function( a, b ) {
                    if ( b ) {
                            while ( (b = b.parentNode) ) {
                                    if ( b === a ) {
                                            return true;
                                    }
                            }
                    }
                    return false;
            };

    /* Sorting
    ---------------------------------------------------------------------- */

    // Document order sorting
    sortOrder = docElem.compareDocumentPosition ?
    function( a, b ) {

            // Flag for duplicate removal
            if ( a === b ) {
                    hasDuplicate = true;
                    return 0;
            }

            var compare = b.compareDocumentPosition && a.compareDocumentPosition && a.compareDocumentPosition( b );

            if ( compare ) {
                    // Disconnected nodes
                    if ( compare & 1 ||
                            (!support.sortDetached && b.compareDocumentPosition( a ) === compare) ) {

                            // Choose the first element that is related to our preferred document
                            if ( a === doc || contains(preferredDoc, a) ) {
                                    return -1;
                            }
                            if ( b === doc || contains(preferredDoc, b) ) {
                                    return 1;
                            }

                            // Maintain original order
                            return sortInput ?
                                    ( indexOf.call( sortInput, a ) - indexOf.call( sortInput, b ) ) :
                                    0;
                    }

                    return compare & 4 ? -1 : 1;
            }

            // Not directly comparable, sort on existence of method
            return a.compareDocumentPosition ? -1 : 1;
    } :
    function( a, b ) {
            var cur,
                    i = 0,
                    aup = a.parentNode,
                    bup = b.parentNode,
                    ap = [ a ],
                    bp = [ b ];

            // Exit early if the nodes are identical
            if ( a === b ) {
                    hasDuplicate = true;
                    return 0;

            // Parentless nodes are either documents or disconnected
            } else if ( !aup || !bup ) {
                    return a === doc ? -1 :
                            b === doc ? 1 :
                            aup ? -1 :
                            bup ? 1 :
                            sortInput ?
                            ( indexOf.call( sortInput, a ) - indexOf.call( sortInput, b ) ) :
                            0;

            // If the nodes are siblings, we can do a quick check
            } else if ( aup === bup ) {
                    return siblingCheck( a, b );
            }

            // Otherwise we need full lists of their ancestors for comparison
            cur = a;
            while ( (cur = cur.parentNode) ) {
                    ap.unshift( cur );
            }
            cur = b;
            while ( (cur = cur.parentNode) ) {
                    bp.unshift( cur );
            }

            // Walk down the tree looking for a discrepancy
            while ( ap[i] === bp[i] ) {
                    i++;
            }

            return i ?
                    // Do a sibling check if the nodes have a common ancestor
                    siblingCheck( ap[i], bp[i] ) :

                    // Otherwise nodes in our document sort first
                    ap[i] === preferredDoc ? -1 :
                    bp[i] === preferredDoc ? 1 :
                    0;
    };

    return doc;
};

Sizzle.matches = function( expr, elements ) {
    return Sizzle( expr, null, null, elements );
};

Sizzle.matchesSelector = function( elem, expr ) {
    // Set document vars if needed
    if ( ( elem.ownerDocument || elem ) !== document ) {
            setDocument( elem );
    }

    // Make sure that attribute selectors are quoted
    expr = expr.replace( rattributeQuotes, "='$1']" );

    if ( support.matchesSelector && documentIsHTML &&
            ( !rbuggyMatches || !rbuggyMatches.test( expr ) ) &&
            ( !rbuggyQSA     || !rbuggyQSA.test( expr ) ) ) {

            try {
                    var ret = matches.call( elem, expr );

                    // IE 9's matchesSelector returns false on disconnected nodes
                    if ( ret || support.disconnectedMatch ||
                                    // As well, disconnected nodes are said to be in a document
                                    // fragment in IE 9
                                    elem.document && elem.document.nodeType !== 11 ) {
                            return ret;
                    }
            } catch(e) {}
    }

    return Sizzle( expr, document, null, [elem] ).length > 0;
};

Sizzle.contains = function( context, elem ) {
    // Set document vars if needed
    if ( ( context.ownerDocument || context ) !== document ) {
            setDocument( context );
    }
    return contains( context, elem );
};

Sizzle.attr = function( elem, name ) {
    // Set document vars if needed
    if ( ( elem.ownerDocument || elem ) !== document ) {
            setDocument( elem );
    }

    var fn = Expr.attrHandle[ name.toLowerCase() ],
            // Don't get fooled by Object.prototype properties (jQuery #13807)
            val = fn && hasOwn.call( Expr.attrHandle, name.toLowerCase() ) ?
                    fn( elem, name, !documentIsHTML ) :
                    undefined;

    return val === undefined ?
            support.attributes || !documentIsHTML ?
                    elem.getAttribute( name ) :
                    (val = elem.getAttributeNode(name)) && val.specified ?
                            val.value :
                            null :
            val;
};

Sizzle.error = function( msg ) {
    throw new Error( "Syntax error, unrecognized expression: " + msg );
};

/**
* Document sorting and removing duplicates
* @param {ArrayLike} results
*/
Sizzle.uniqueSort = function( results ) {
    var elem,
            duplicates = [],
            j = 0,
            i = 0;

    // Unless we *know* we can detect duplicates, assume their presence
    hasDuplicate = !support.detectDuplicates;
    sortInput = !support.sortStable && results.slice( 0 );
    results.sort( sortOrder );

    if ( hasDuplicate ) {
            while ( (elem = results[i++]) ) {
                    if ( elem === results[ i ] ) {
                            j = duplicates.push( i );
                    }
            }
            while ( j-- ) {
                    results.splice( duplicates[ j ], 1 );
            }
    }

    return results;
};

/**
* Utility function for retrieving the text value of an array of DOM nodes
* @param {Array|Element} elem
*/
getText = Sizzle.getText = function( elem ) {
    var node,
            ret = "",
            i = 0,
            nodeType = elem.nodeType;

    if ( !nodeType ) {
            // If no nodeType, this is expected to be an array
            for ( ; (node = elem[i]); i++ ) {
                    // Do not traverse comment nodes
                    ret += getText( node );
            }
    } else if ( nodeType === 1 || nodeType === 9 || nodeType === 11 ) {
            // Use textContent for elements
            // innerText usage removed for consistency of new lines (see #11153)
            if ( typeof elem.textContent === "string" ) {
                    return elem.textContent;
            } else {
                    // Traverse its children
                    for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
                            ret += getText( elem );
                    }
            }
    } else if ( nodeType === 3 || nodeType === 4 ) {
            return elem.nodeValue;
    }
    // Do not include comment or processing instruction nodes

    return ret;
};

Expr = Sizzle.selectors = {

    // Can be adjusted by the user
    cacheLength: 50,

    createPseudo: markFunction,

    match: matchExpr,

    attrHandle: {},

    find: {},

    relative: {
            ">": { dir: "parentNode", first: true },
            " ": { dir: "parentNode" },
            "+": { dir: "previousSibling", first: true },
            "~": { dir: "previousSibling" }
    },

    preFilter: {
            "ATTR": function( match ) {
                    match[1] = match[1].replace( runescape, funescape );

                    // Move the given value to match[3] whether quoted or unquoted
                    match[3] = ( match[4] || match[5] || "" ).replace( runescape, funescape );

                    if ( match[2] === "~=" ) {
                            match[3] = " " + match[3] + " ";
                    }

                    return match.slice( 0, 4 );
            },

            "CHILD": function( match ) {
                    /* matches from matchExpr["CHILD"]
                            1 type (only|nth|...)
                            2 what (child|of-type)
                            3 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
                            4 xn-component of xn+y argument ([+-]?\d*n|)
                            5 sign of xn-component
                            6 x of xn-component
                            7 sign of y-component
                            8 y of y-component
                    */
                    match[1] = match[1].toLowerCase();

                    if ( match[1].slice( 0, 3 ) === "nth" ) {
                            // nth-* requires argument
                            if ( !match[3] ) {
                                    Sizzle.error( match[0] );
                            }

                            // numeric x and y parameters for Expr.filter.CHILD
                            // remember that false/true cast respectively to 0/1
                            match[4] = +( match[4] ? match[5] + (match[6] || 1) : 2 * ( match[3] === "even" || match[3] === "odd" ) );
                            match[5] = +( ( match[7] + match[8] ) || match[3] === "odd" );

                    // other types prohibit arguments
                    } else if ( match[3] ) {
                            Sizzle.error( match[0] );
                    }

                    return match;
            },

            "PSEUDO": function( match ) {
                    var excess,
                            unquoted = !match[5] && match[2];

                    if ( matchExpr["CHILD"].test( match[0] ) ) {
                            return null;
                    }

                    // Accept quoted arguments as-is
                    if ( match[3] && match[4] !== undefined ) {
                            match[2] = match[4];

                    // Strip excess characters from unquoted arguments
                    } else if ( unquoted && rpseudo.test( unquoted ) &&
                            // Get excess from tokenize (recursively)
                            (excess = tokenize( unquoted, true )) &&
                            // advance to the next closing parenthesis
                            (excess = unquoted.indexOf( ")", unquoted.length - excess ) - unquoted.length) ) {

                            // excess is a negative index
                            match[0] = match[0].slice( 0, excess );
                            match[2] = unquoted.slice( 0, excess );
                    }

                    // Return only captures needed by the pseudo filter method (type and argument)
                    return match.slice( 0, 3 );
            }
    },

    filter: {

            "TAG": function( nodeNameSelector ) {
                    var nodeName = nodeNameSelector.replace( runescape, funescape ).toLowerCase();
                    return nodeNameSelector === "*" ?
                            function() { return true; } :
                            function( elem ) {
                                    return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
                            };
            },

            "CLASS": function( className ) {
                    var pattern = classCache[ className + " " ];

                    return pattern ||
                            (pattern = new RegExp( "(^|" + whitespace + ")" + className + "(" + whitespace + "|$)" )) &&
                            classCache( className, function( elem ) {
                                    return pattern.test( typeof elem.className === "string" && elem.className || typeof elem.getAttribute !== strundefined && elem.getAttribute("class") || "" );
                            });
            },

            "ATTR": function( name, operator, check ) {
                    return function( elem ) {
                            var result = Sizzle.attr( elem, name );

                            if ( result == null ) {
                                    return operator === "!=";
                            }
                            if ( !operator ) {
                                    return true;
                            }

                            result += "";

                            return operator === "=" ? result === check :
                                    operator === "!=" ? result !== check :
                                    operator === "^=" ? check && result.indexOf( check ) === 0 :
                                    operator === "*=" ? check && result.indexOf( check ) > -1 :
                                    operator === "$=" ? check && result.slice( -check.length ) === check :
                                    operator === "~=" ? ( " " + result + " " ).indexOf( check ) > -1 :
                                    operator === "|=" ? result === check || result.slice( 0, check.length + 1 ) === check + "-" :
                                    false;
                    };
            },

            "CHILD": function( type, what, argument, first, last ) {
                    var simple = type.slice( 0, 3 ) !== "nth",
                            forward = type.slice( -4 ) !== "last",
                            ofType = what === "of-type";

                    return first === 1 && last === 0 ?

                            // Shortcut for :nth-*(n)
                            function( elem ) {
                                    return !!elem.parentNode;
                            } :

                            function( elem, context, xml ) {
                                    var cache, outerCache, node, diff, nodeIndex, start,
                                            dir = simple !== forward ? "nextSibling" : "previousSibling",
                                            parent = elem.parentNode,
                                            name = ofType && elem.nodeName.toLowerCase(),
                                            useCache = !xml && !ofType;

                                    if ( parent ) {

                                            // :(first|last|only)-(child|of-type)
                                            if ( simple ) {
                                                    while ( dir ) {
                                                            node = elem;
                                                            while ( (node = node[ dir ]) ) {
                                                                    if ( ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1 ) {
                                                                            return false;
                                                                    }
                                                            }
                                                            // Reverse direction for :only-* (if we haven't yet done so)
                                                            start = dir = type === "only" && !start && "nextSibling";
                                                    }
                                                    return true;
                                            }

                                            start = [ forward ? parent.firstChild : parent.lastChild ];

                                            // non-xml :nth-child(...) stores cache data on `parent`
                                            if ( forward && useCache ) {
                                                    // Seek `elem` from a previously-cached index
                                                    outerCache = parent[ expando ] || (parent[ expando ] = {});
                                                    cache = outerCache[ type ] || [];
                                                    nodeIndex = cache[0] === dirruns && cache[1];
                                                    diff = cache[0] === dirruns && cache[2];
                                                    node = nodeIndex && parent.childNodes[ nodeIndex ];

                                                    while ( (node = ++nodeIndex && node && node[ dir ] ||

                                                            // Fallback to seeking `elem` from the start
                                                            (diff = nodeIndex = 0) || start.pop()) ) {

                                                            // When found, cache indexes on `parent` and break
                                                            if ( node.nodeType === 1 && ++diff && node === elem ) {
                                                                    outerCache[ type ] = [ dirruns, nodeIndex, diff ];
                                                                    break;
                                                            }
                                                    }

                                            // Use previously-cached element index if available
                                            } else if ( useCache && (cache = (elem[ expando ] || (elem[ expando ] = {}))[ type ]) && cache[0] === dirruns ) {
                                                    diff = cache[1];

                                            // xml :nth-child(...) or :nth-last-child(...) or :nth(-last)?-of-type(...)
                                            } else {
                                                    // Use the same loop as above to seek `elem` from the start
                                                    while ( (node = ++nodeIndex && node && node[ dir ] ||
                                                            (diff = nodeIndex = 0) || start.pop()) ) {

                                                            if ( ( ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1 ) && ++diff ) {
                                                                    // Cache the index of each encountered element
                                                                    if ( useCache ) {
                                                                            (node[ expando ] || (node[ expando ] = {}))[ type ] = [ dirruns, diff ];
                                                                    }

                                                                    if ( node === elem ) {
                                                                            break;
                                                                    }
                                                            }
                                                    }
                                            }

                                            // Incorporate the offset, then check against cycle size
                                            diff -= last;
                                            return diff === first || ( diff % first === 0 && diff / first >= 0 );
                                    }
                            };
            },

            "PSEUDO": function( pseudo, argument ) {
                    // pseudo-class names are case-insensitive
                    // http://www.w3.org/TR/selectors/#pseudo-classes
                    // Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
                    // Remember that setFilters inherits from pseudos
                    var args,
                            fn = Expr.pseudos[ pseudo ] || Expr.setFilters[ pseudo.toLowerCase() ] ||
                                    Sizzle.error( "unsupported pseudo: " + pseudo );

                    // The user may use createPseudo to indicate that
                    // arguments are needed to create the filter function
                    // just as Sizzle does
                    if ( fn[ expando ] ) {
                            return fn( argument );
                    }

                    // But maintain support for old signatures
                    if ( fn.length > 1 ) {
                            args = [ pseudo, pseudo, "", argument ];
                            return Expr.setFilters.hasOwnProperty( pseudo.toLowerCase() ) ?
                                    markFunction(function( seed, matches ) {
                                            var idx,
                                                    matched = fn( seed, argument ),
                                                    i = matched.length;
                                            while ( i-- ) {
                                                    idx = indexOf.call( seed, matched[i] );
                                                    seed[ idx ] = !( matches[ idx ] = matched[i] );
                                            }
                                    }) :
                                    function( elem ) {
                                            return fn( elem, 0, args );
                                    };
                    }

                    return fn;
            }
    },

    pseudos: {
            // Potentially complex pseudos
            "not": markFunction(function( selector ) {
                    // Trim the selector passed to compile
                    // to avoid treating leading and trailing
                    // spaces as combinators
                    var input = [],
                            results = [],
                            matcher = compile( selector.replace( rtrim, "$1" ) );

                    return matcher[ expando ] ?
                            markFunction(function( seed, matches, context, xml ) {
                                    var elem,
                                            unmatched = matcher( seed, null, xml, [] ),
                                            i = seed.length;

                                    // Match elements unmatched by `matcher`
                                    while ( i-- ) {
                                            if ( (elem = unmatched[i]) ) {
                                                    seed[i] = !(matches[i] = elem);
                                            }
                                    }
                            }) :
                            function( elem, context, xml ) {
                                    input[0] = elem;
                                    matcher( input, null, xml, results );
                                    return !results.pop();
                            };
            }),

            "has": markFunction(function( selector ) {
                    return function( elem ) {
                            return Sizzle( selector, elem ).length > 0;
                    };
            }),

            "contains": markFunction(function( text ) {
                    return function( elem ) {
                            return ( elem.textContent || elem.innerText || getText( elem ) ).indexOf( text ) > -1;
                    };
            }),

            // "Whether an element is represented by a :lang() selector
            // is based solely on the element's language value
            // being equal to the identifier C,
            // or beginning with the identifier C immediately followed by "-".
            // The matching of C against the element's language value is performed case-insensitively.
            // The identifier C does not have to be a valid language name."
            // http://www.w3.org/TR/selectors/#lang-pseudo
            "lang": markFunction( function( lang ) {
                    // lang value must be a valid identifier
                    if ( !ridentifier.test(lang || "") ) {
                            Sizzle.error( "unsupported lang: " + lang );
                    }
                    lang = lang.replace( runescape, funescape ).toLowerCase();
                    return function( elem ) {
                            var elemLang;
                            do {
                                    if ( (elemLang = documentIsHTML ?
                                            elem.lang :
                                            elem.getAttribute("xml:lang") || elem.getAttribute("lang")) ) {

                                            elemLang = elemLang.toLowerCase();
                                            return elemLang === lang || elemLang.indexOf( lang + "-" ) === 0;
                                    }
                            } while ( (elem = elem.parentNode) && elem.nodeType === 1 );
                            return false;
                    };
            }),

            // Miscellaneous
            "target": function( elem ) {
                    var hash = window.location && window.location.hash;
                    return hash && hash.slice( 1 ) === elem.id;
            },

            "root": function( elem ) {
                    return elem === docElem;
            },

            "focus": function( elem ) {
                    return elem === document.activeElement && (!document.hasFocus || document.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
            },

            // Boolean properties
            "enabled": function( elem ) {
                    return elem.disabled === false;
            },

            "disabled": function( elem ) {
                    return elem.disabled === true;
            },

            "checked": function( elem ) {
                    // In CSS3, :checked should return both checked and selected elements
                    // http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
                    var nodeName = elem.nodeName.toLowerCase();
                    return (nodeName === "input" && !!elem.checked) || (nodeName === "option" && !!elem.selected);
            },

            "selected": function( elem ) {
                    // Accessing this property makes selected-by-default
                    // options in Safari work properly
                    if ( elem.parentNode ) {
                            elem.parentNode.selectedIndex;
                    }

                    return elem.selected === true;
            },

            // Contents
            "empty": function( elem ) {
                    // http://www.w3.org/TR/selectors/#empty-pseudo
                    // :empty is only affected by element nodes and content nodes(including text(3), cdata(4)),
                    //   not comment, processing instructions, or others
                    // Thanks to Diego Perini for the nodeName shortcut
                    //   Greater than "@" means alpha characters (specifically not starting with "#" or "?")
                    for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
                            if ( elem.nodeName > "@" || elem.nodeType === 3 || elem.nodeType === 4 ) {
                                    return false;
                            }
                    }
                    return true;
            },

            "parent": function( elem ) {
                    return !Expr.pseudos["empty"]( elem );
            },

            // Element/input types
            "header": function( elem ) {
                    return rheader.test( elem.nodeName );
            },

            "input": function( elem ) {
                    return rinputs.test( elem.nodeName );
            },

            "button": function( elem ) {
                    var name = elem.nodeName.toLowerCase();
                    return name === "input" && elem.type === "button" || name === "button";
            },

            "text": function( elem ) {
                    var attr;
                    // IE6 and 7 will map elem.type to 'text' for new HTML5 types (search, etc)
                    // use getAttribute instead to test this case
                    return elem.nodeName.toLowerCase() === "input" &&
                            elem.type === "text" &&
                            ( (attr = elem.getAttribute("type")) == null || attr.toLowerCase() === elem.type );
            },

            // Position-in-collection
            "first": createPositionalPseudo(function() {
                    return [ 0 ];
            }),

            "last": createPositionalPseudo(function( matchIndexes, length ) {
                    return [ length - 1 ];
            }),

            "eq": createPositionalPseudo(function( matchIndexes, length, argument ) {
                    return [ argument < 0 ? argument + length : argument ];
            }),

            "even": createPositionalPseudo(function( matchIndexes, length ) {
                    var i = 0;
                    for ( ; i < length; i += 2 ) {
                            matchIndexes.push( i );
                    }
                    return matchIndexes;
            }),

            "odd": createPositionalPseudo(function( matchIndexes, length ) {
                    var i = 1;
                    for ( ; i < length; i += 2 ) {
                            matchIndexes.push( i );
                    }
                    return matchIndexes;
            }),

            "lt": createPositionalPseudo(function( matchIndexes, length, argument ) {
                    var i = argument < 0 ? argument + length : argument;
                    for ( ; --i >= 0; ) {
                            matchIndexes.push( i );
                    }
                    return matchIndexes;
            }),

            "gt": createPositionalPseudo(function( matchIndexes, length, argument ) {
                    var i = argument < 0 ? argument + length : argument;
                    for ( ; ++i < length; ) {
                            matchIndexes.push( i );
                    }
                    return matchIndexes;
            })
    }
};

Expr.pseudos["nth"] = Expr.pseudos["eq"];

for ( i in { radio: true, checkbox: true, file: true, password: true, image: true } ) {
    Expr.pseudos[ i ] = createInputPseudo( i );
}
for ( i in { submit: true, reset: true } ) {
    Expr.pseudos[ i ] = createButtonPseudo( i );
}

function setFilters() {}
setFilters.prototype = Expr.filters = Expr.pseudos;
Expr.setFilters = new setFilters();

function tokenize( selector, parseOnly ) {
    var matched, match, tokens, type,
            soFar, groups, preFilters,
            cached = tokenCache[ selector + " " ];

    if ( cached ) {
            return parseOnly ? 0 : cached.slice( 0 );
    }

    soFar = selector;
    groups = [];
    preFilters = Expr.preFilter;

    while ( soFar ) {

            // Comma and first run
            if ( !matched || (match = rcomma.exec( soFar )) ) {
                    if ( match ) {
                            // Don't consume trailing commas as valid
                            soFar = soFar.slice( match[0].length ) || soFar;
                    }
                    groups.push( tokens = [] );
            }

            matched = false;

            // Combinators
            if ( (match = rcombinators.exec( soFar )) ) {
                    matched = match.shift();
                    tokens.push({
                            value: matched,
                            // Cast descendant combinators to space
                            type: match[0].replace( rtrim, " " )
                    });
                    soFar = soFar.slice( matched.length );
            }

            // Filters
            for ( type in Expr.filter ) {
                    if ( (match = matchExpr[ type ].exec( soFar )) && (!preFilters[ type ] ||
                            (match = preFilters[ type ]( match ))) ) {
                            matched = match.shift();
                            tokens.push({
                                    value: matched,
                                    type: type,
                                    matches: match
                            });
                            soFar = soFar.slice( matched.length );
                    }
            }

            if ( !matched ) {
                    break;
            }
    }

    // Return the length of the invalid excess
    // if we're just parsing
    // Otherwise, throw an error or return tokens
    return parseOnly ?
            soFar.length :
            soFar ?
                    Sizzle.error( selector ) :
                    // Cache the tokens
                    tokenCache( selector, groups ).slice( 0 );
}

function toSelector( tokens ) {
    var i = 0,
            len = tokens.length,
            selector = "";
    for ( ; i < len; i++ ) {
            selector += tokens[i].value;
    }
    return selector;
}

function addCombinator( matcher, combinator, base ) {
    var dir = combinator.dir,
            checkNonElements = base && dir === "parentNode",
            doneName = done++;

    return combinator.first ?
            // Check against closest ancestor/preceding element
            function( elem, context, xml ) {
                    while ( (elem = elem[ dir ]) ) {
                            if ( elem.nodeType === 1 || checkNonElements ) {
                                    return matcher( elem, context, xml );
                            }
                    }
            } :

            // Check against all ancestor/preceding elements
            function( elem, context, xml ) {
                    var data, cache, outerCache,
                            dirkey = dirruns + " " + doneName;

                    // We can't set arbitrary data on XML nodes, so they don't benefit from dir caching
                    if ( xml ) {
                            while ( (elem = elem[ dir ]) ) {
                                    if ( elem.nodeType === 1 || checkNonElements ) {
                                            if ( matcher( elem, context, xml ) ) {
                                                    return true;
                                            }
                                    }
                            }
                    } else {
                            while ( (elem = elem[ dir ]) ) {
                                    if ( elem.nodeType === 1 || checkNonElements ) {
                                            outerCache = elem[ expando ] || (elem[ expando ] = {});
                                            if ( (cache = outerCache[ dir ]) && cache[0] === dirkey ) {
                                                    if ( (data = cache[1]) === true || data === cachedruns ) {
                                                            return data === true;
                                                    }
                                            } else {
                                                    cache = outerCache[ dir ] = [ dirkey ];
                                                    cache[1] = matcher( elem, context, xml ) || cachedruns;
                                                    if ( cache[1] === true ) {
                                                            return true;
                                                    }
                                            }
                                    }
                            }
                    }
            };
}

function elementMatcher( matchers ) {
    return matchers.length > 1 ?
            function( elem, context, xml ) {
                    var i = matchers.length;
                    while ( i-- ) {
                            if ( !matchers[i]( elem, context, xml ) ) {
                                    return false;
                            }
                    }
                    return true;
            } :
            matchers[0];
}

function condense( unmatched, map, filter, context, xml ) {
    var elem,
            newUnmatched = [],
            i = 0,
            len = unmatched.length,
            mapped = map != null;

    for ( ; i < len; i++ ) {
            if ( (elem = unmatched[i]) ) {
                    if ( !filter || filter( elem, context, xml ) ) {
                            newUnmatched.push( elem );
                            if ( mapped ) {
                                    map.push( i );
                            }
                    }
            }
    }

    return newUnmatched;
}

function setMatcher( preFilter, selector, matcher, postFilter, postFinder, postSelector ) {
    if ( postFilter && !postFilter[ expando ] ) {
            postFilter = setMatcher( postFilter );
    }
    if ( postFinder && !postFinder[ expando ] ) {
            postFinder = setMatcher( postFinder, postSelector );
    }
    return markFunction(function( seed, results, context, xml ) {
            var temp, i, elem,
                    preMap = [],
                    postMap = [],
                    preexisting = results.length,

                    // Get initial elements from seed or context
                    elems = seed || multipleContexts( selector || "*", context.nodeType ? [ context ] : context, [] ),

                    // Prefilter to get matcher input, preserving a map for seed-results synchronization
                    matcherIn = preFilter && ( seed || !selector ) ?
                            condense( elems, preMap, preFilter, context, xml ) :
                            elems,

                    matcherOut = matcher ?
                            // If we have a postFinder, or filtered seed, or non-seed postFilter or preexisting results,
                            postFinder || ( seed ? preFilter : preexisting || postFilter ) ?

                                    // ...intermediate processing is necessary
                                    [] :

                                    // ...otherwise use results directly
                                    results :
                            matcherIn;

            // Find primary matches
            if ( matcher ) {
                    matcher( matcherIn, matcherOut, context, xml );
            }

            // Apply postFilter
            if ( postFilter ) {
                    temp = condense( matcherOut, postMap );
                    postFilter( temp, [], context, xml );

                    // Un-match failing elements by moving them back to matcherIn
                    i = temp.length;
                    while ( i-- ) {
                            if ( (elem = temp[i]) ) {
                                    matcherOut[ postMap[i] ] = !(matcherIn[ postMap[i] ] = elem);
                            }
                    }
            }

            if ( seed ) {
                    if ( postFinder || preFilter ) {
                            if ( postFinder ) {
                                    // Get the final matcherOut by condensing this intermediate into postFinder contexts
                                    temp = [];
                                    i = matcherOut.length;
                                    while ( i-- ) {
                                            if ( (elem = matcherOut[i]) ) {
                                                    // Restore matcherIn since elem is not yet a final match
                                                    temp.push( (matcherIn[i] = elem) );
                                            }
                                    }
                                    postFinder( null, (matcherOut = []), temp, xml );
                            }

                            // Move matched elements from seed to results to keep them synchronized
                            i = matcherOut.length;
                            while ( i-- ) {
                                    if ( (elem = matcherOut[i]) &&
                                            (temp = postFinder ? indexOf.call( seed, elem ) : preMap[i]) > -1 ) {

                                            seed[temp] = !(results[temp] = elem);
                                    }
                            }
                    }

            // Add elements to results, through postFinder if defined
            } else {
                    matcherOut = condense(
                            matcherOut === results ?
                                    matcherOut.splice( preexisting, matcherOut.length ) :
                                    matcherOut
                    );
                    if ( postFinder ) {
                            postFinder( null, results, matcherOut, xml );
                    } else {
                            push.apply( results, matcherOut );
                    }
            }
    });
}

function matcherFromTokens( tokens ) {
    var checkContext, matcher, j,
            len = tokens.length,
            leadingRelative = Expr.relative[ tokens[0].type ],
            implicitRelative = leadingRelative || Expr.relative[" "],
            i = leadingRelative ? 1 : 0,

            // The foundational matcher ensures that elements are reachable from top-level context(s)
            matchContext = addCombinator( function( elem ) {
                    return elem === checkContext;
            }, implicitRelative, true ),
            matchAnyContext = addCombinator( function( elem ) {
                    return indexOf.call( checkContext, elem ) > -1;
            }, implicitRelative, true ),
            matchers = [ function( elem, context, xml ) {
                    return ( !leadingRelative && ( xml || context !== outermostContext ) ) || (
                            (checkContext = context).nodeType ?
                                    matchContext( elem, context, xml ) :
                                    matchAnyContext( elem, context, xml ) );
            } ];

    for ( ; i < len; i++ ) {
            if ( (matcher = Expr.relative[ tokens[i].type ]) ) {
                    matchers = [ addCombinator(elementMatcher( matchers ), matcher) ];
            } else {
                    matcher = Expr.filter[ tokens[i].type ].apply( null, tokens[i].matches );

                    // Return special upon seeing a positional matcher
                    if ( matcher[ expando ] ) {
                            // Find the next relative operator (if any) for proper handling
                            j = ++i;
                            for ( ; j < len; j++ ) {
                                    if ( Expr.relative[ tokens[j].type ] ) {
                                            break;
                                    }
                            }
                            return setMatcher(
                                    i > 1 && elementMatcher( matchers ),
                                    i > 1 && toSelector(
                                            // If the preceding token was a descendant combinator, insert an implicit any-element `*`
                                            tokens.slice( 0, i - 1 ).concat({ value: tokens[ i - 2 ].type === " " ? "*" : "" })
                                    ).replace( rtrim, "$1" ),
                                    matcher,
                                    i < j && matcherFromTokens( tokens.slice( i, j ) ),
                                    j < len && matcherFromTokens( (tokens = tokens.slice( j )) ),
                                    j < len && toSelector( tokens )
                            );
                    }
                    matchers.push( matcher );
            }
    }

    return elementMatcher( matchers );
}

function matcherFromGroupMatchers( elementMatchers, setMatchers ) {
    // A counter to specify which element is currently being matched
    var matcherCachedRuns = 0,
            bySet = setMatchers.length > 0,
            byElement = elementMatchers.length > 0,
            superMatcher = function( seed, context, xml, results, expandContext ) {
                    var elem, j, matcher,
                            setMatched = [],
                            matchedCount = 0,
                            i = "0",
                            unmatched = seed && [],
                            outermost = expandContext != null,
                            contextBackup = outermostContext,
                            // We must always have either seed elements or context
                            elems = seed || byElement && Expr.find["TAG"]( "*", expandContext && context.parentNode || context ),
                            // Use integer dirruns iff this is the outermost matcher
                            dirrunsUnique = (dirruns += contextBackup == null ? 1 : Math.random() || 0.1);

                    if ( outermost ) {
                            outermostContext = context !== document && context;
                            cachedruns = matcherCachedRuns;
                    }

                    // Add elements passing elementMatchers directly to results
                    // Keep `i` a string if there are no elements so `matchedCount` will be "00" below
                    for ( ; (elem = elems[i]) != null; i++ ) {
                            if ( byElement && elem ) {
                                    j = 0;
                                    while ( (matcher = elementMatchers[j++]) ) {
                                            if ( matcher( elem, context, xml ) ) {
                                                    results.push( elem );
                                                    break;
                                            }
                                    }
                                    if ( outermost ) {
                                            dirruns = dirrunsUnique;
                                            cachedruns = ++matcherCachedRuns;
                                    }
                            }

                            // Track unmatched elements for set filters
                            if ( bySet ) {
                                    // They will have gone through all possible matchers
                                    if ( (elem = !matcher && elem) ) {
                                            matchedCount--;
                                    }

                                    // Lengthen the array for every element, matched or not
                                    if ( seed ) {
                                            unmatched.push( elem );
                                    }
                            }
                    }

                    // Apply set filters to unmatched elements
                    matchedCount += i;
                    if ( bySet && i !== matchedCount ) {
                            j = 0;
                            while ( (matcher = setMatchers[j++]) ) {
                                    matcher( unmatched, setMatched, context, xml );
                            }

                            if ( seed ) {
                                    // Reintegrate element matches to eliminate the need for sorting
                                    if ( matchedCount > 0 ) {
                                            while ( i-- ) {
                                                    if ( !(unmatched[i] || setMatched[i]) ) {
                                                            setMatched[i] = pop.call( results );
                                                    }
                                            }
                                    }

                                    // Discard index placeholder values to get only actual matches
                                    setMatched = condense( setMatched );
                            }

                            // Add matches to results
                            push.apply( results, setMatched );

                            // Seedless set matches succeeding multiple successful matchers stipulate sorting
                            if ( outermost && !seed && setMatched.length > 0 &&
                                    ( matchedCount + setMatchers.length ) > 1 ) {

                                    Sizzle.uniqueSort( results );
                            }
                    }

                    // Override manipulation of globals by nested matchers
                    if ( outermost ) {
                            dirruns = dirrunsUnique;
                            outermostContext = contextBackup;
                    }

                    return unmatched;
            };

    return bySet ?
            markFunction( superMatcher ) :
            superMatcher;
}

compile = Sizzle.compile = function( selector, group /* Internal Use Only */ ) {
    var i,
            setMatchers = [],
            elementMatchers = [],
            cached = compilerCache[ selector + " " ];

    if ( !cached ) {
            // Generate a function of recursive functions that can be used to check each element
            if ( !group ) {
                    group = tokenize( selector );
            }
            i = group.length;
            while ( i-- ) {
                    cached = matcherFromTokens( group[i] );
                    if ( cached[ expando ] ) {
                            setMatchers.push( cached );
                    } else {
                            elementMatchers.push( cached );
                    }
            }

            // Cache the compiled function
            cached = compilerCache( selector, matcherFromGroupMatchers( elementMatchers, setMatchers ) );
    }
    return cached;
};

function multipleContexts( selector, contexts, results ) {
    var i = 0,
            len = contexts.length;
    for ( ; i < len; i++ ) {
            Sizzle( selector, contexts[i], results );
    }
    return results;
}

function select( selector, context, results, seed ) {
    var i, tokens, token, type, find,
            match = tokenize( selector );

    if ( !seed ) {
            // Try to minimize operations if there is only one group
            if ( match.length === 1 ) {

                    // Take a shortcut and set the context if the root selector is an ID
                    tokens = match[0] = match[0].slice( 0 );
                    if ( tokens.length > 2 && (token = tokens[0]).type === "ID" &&
                                    support.getById && context.nodeType === 9 && documentIsHTML &&
                                    Expr.relative[ tokens[1].type ] ) {

                            context = ( Expr.find["ID"]( token.matches[0].replace(runescape, funescape), context ) || [] )[0];
                            if ( !context ) {
                                    return results;
                            }
                            selector = selector.slice( tokens.shift().value.length );
                    }

                    // Fetch a seed set for right-to-left matching
                    i = matchExpr["needsContext"].test( selector ) ? 0 : tokens.length;
                    while ( i-- ) {
                            token = tokens[i];

                            // Abort if we hit a combinator
                            if ( Expr.relative[ (type = token.type) ] ) {
                                    break;
                            }
                            if ( (find = Expr.find[ type ]) ) {
                                    // Search, expanding context for leading sibling combinators
                                    if ( (seed = find(
                                            token.matches[0].replace( runescape, funescape ),
                                            rsibling.test( tokens[0].type ) && context.parentNode || context
                                    )) ) {

                                            // If seed is empty or no tokens remain, we can return early
                                            tokens.splice( i, 1 );
                                            selector = seed.length && toSelector( tokens );
                                            if ( !selector ) {
                                                    push.apply( results, seed );
                                                    return results;
                                            }

                                            break;
                                    }
                            }
                    }
            }
    }
    compile( selector, match )(
            seed,
            context,
            !documentIsHTML,
            results,
            rsibling.test( selector )
    );
    return results;
}

support.sortStable = expando.split("").sort( sortOrder ).join("") === expando;
support.detectDuplicates = hasDuplicate;
setDocument();

// Support: Webkit<537.32 - Safari 6.0.3/Chrome 25 (fixed in Chrome 27)
// Detached nodes confoundingly follow *each other*
support.sortDetached = assert(function( div1 ) {
    // Should return 1, but returns 4 (following)
    return div1.compareDocumentPosition( document.createElement("div") ) & 1;
});

// Support: IE<8
// Prevent attribute/property "interpolation"
// http://msdn.microsoft.com/en-us/library/ms536429%28VS.85%29.aspx
if ( !assert(function( div ) {
    div.innerHTML = "<a href='#'></a>";
    return div.firstChild.getAttribute("href") === "#" ;
}) ) {
    addHandle( "type|href|height|width", function( elem, name, isXML ) {
            if ( !isXML ) {
                    return elem.getAttribute( name, name.toLowerCase() === "type" ? 1 : 2 );
            }
    });
}

if ( !support.attributes || !assert(function( div ) {
    div.innerHTML = "<input/>";
    div.firstChild.setAttribute( "value", "" );
    return div.firstChild.getAttribute( "value" ) === "";
}) ) {
    addHandle( "value", function( elem, name, isXML ) {
            if ( !isXML && elem.nodeName.toLowerCase() === "input" ) {
                    return elem.defaultValue;
            }
    });
}

if ( !assert(function( div ) {
    return div.getAttribute("disabled") == null;
}) ) {
    addHandle( booleans, function( elem, name, isXML ) {
            var val;
            if ( !isXML ) {
                    return (val = elem.getAttributeNode( name )) && val.specified ?
                            val.value :
                            elem[ name ] === true ? name.toLowerCase() : null;
            }
    });
}

jQuery.find = Sizzle;
jQuery.expr = Sizzle.selectors;
jQuery.expr[":"] = jQuery.expr.pseudos;
jQuery.unique = Sizzle.uniqueSort;
jQuery.text = Sizzle.getText;
jQuery.isXMLDoc = Sizzle.isXML;
jQuery.contains = Sizzle.contains;

})( window );
// String to Object options format cache
var optionsCache = {};

// Convert String-formatted options into Object-formatted ones and store in cache
function createOptions( options ) {
    var object = optionsCache[ options ] = {};
    jQuery.each( options.match( core_rnotwhite ) || [], function( _, flag ) {
            object[ flag ] = true;
    });
    return object;
}

jQuery.Callbacks = function( options ) {

    options = typeof options === "string" ?
            ( optionsCache[ options ] || createOptions( options ) ) :
            jQuery.extend( {}, options );

    var
            memory,
            fired,
            firing,
            firingStart,
            firingLength,
            firingIndex,
            list = [],
            stack = !options.once && [],
            fire = function( data ) {
                    memory = options.memory && data;
                    fired = true;
                    firingIndex = firingStart || 0;
                    firingStart = 0;
                    firingLength = list.length;
                    firing = true;
                    for ( ; list && firingIndex < firingLength; firingIndex++ ) {
                            if ( list[ firingIndex ].apply( data[ 0 ], data[ 1 ] ) === false && options.stopOnFalse ) {
                                    memory = false; // To prevent further calls using add
                                    break;
                            }
                    }
                    firing = false;
                    if ( list ) {
                            if ( stack ) {
                                    if ( stack.length ) {
                                            fire( stack.shift() );
                                    }
                            } else if ( memory ) {
                                    list = [];
                            } else {
                                    self.disable();
                            }
                    }
            },
            self = {
                    add: function() {
                            if ( list ) {
                                    // First, we save the current length
                                    var start = list.length;
                                    (function add( args ) {
                                            jQuery.each( args, function( _, arg ) {
                                                    var type = jQuery.type( arg );
                                                    if ( type === "function" ) {
                                                            if ( !options.unique || !self.has( arg ) ) {
                                                                    list.push( arg );
                                                            }
                                                    } else if ( arg && arg.length && type !== "string" ) {
                                                            // Inspect recursively
                                                            add( arg );
                                                    }
                                            });
                                    })( arguments );
                                    // Do we need to add the callbacks to the
                                    // current firing batch?
                                    if ( firing ) {
                                            firingLength = list.length;
                                    // With memory, if we're not firing then
                                    // we should call right away
                                    } else if ( memory ) {
                                            firingStart = start;
                                            fire( memory );
                                    }
                            }
                            return this;
                    },
                    // Remove a callback from the list
                    remove: function() {
                            if ( list ) {
                                    jQuery.each( arguments, function( _, arg ) {
                                            var index;
                                            while( ( index = jQuery.inArray( arg, list, index ) ) > -1 ) {
                                                    list.splice( index, 1 );
                                                    // Handle firing indexes
                                                    if ( firing ) {
                                                            if ( index <= firingLength ) {
                                                                    firingLength--;
                                                            }
                                                            if ( index <= firingIndex ) {
                                                                    firingIndex--;
                                                            }
                                                    }
                                            }
                                    });
                            }
                            return this;
                    },
                    has: function( fn ) {
                            return fn ? jQuery.inArray( fn, list ) > -1 : !!( list && list.length );
                    },
                    empty: function() {
                            list = [];
                            firingLength = 0;
                            return this;
                    },
                    disable: function() {
                            list = stack = memory = undefined;
                            return this;
                    },
                    disabled: function() {
                            return !list;
                    },
                    lock: function() {
                            stack = undefined;
                            if ( !memory ) {
                                    self.disable();
                            }
                            return this;
                    },
                    locked: function() {
                            return !stack;
                    },
                    fireWith: function( context, args ) {
                            if ( list && ( !fired || stack ) ) {
                                    args = args || [];
                                    args = [ context, args.slice ? args.slice() : args ];
                                    if ( firing ) {
                                            stack.push( args );
                                    } else {
                                            fire( args );
                                    }
                            }
                            return this;
                    },
                    fire: function() {
                            self.fireWith( this, arguments );
                            return this;
                    },
                    fired: function() {
                            return !!fired;
                    }
            };

    return self;
};
jQuery.extend({

    Deferred: function( func ) {
            var tuples = [
                            [ "resolve", "done", jQuery.Callbacks("once memory"), "resolved" ],
                            [ "reject", "fail", jQuery.Callbacks("once memory"), "rejected" ],
                            [ "notify", "progress", jQuery.Callbacks("memory") ]
                    ],
                    state = "pending",
                    promise = {
                            state: function() {
                                    return state;
                            },
                            always: function() {
                                    deferred.done( arguments ).fail( arguments );
                                    return this;
                            },
                            then: function( /* fnDone, fnFail, fnProgress */ ) {
                                    var fns = arguments;
                                    return jQuery.Deferred(function( newDefer ) {
                                            jQuery.each( tuples, function( i, tuple ) {
                                                    var action = tuple[ 0 ],
                                                            fn = jQuery.isFunction( fns[ i ] ) && fns[ i ];
                                                    // deferred[ done | fail | progress ] for forwarding actions to newDefer
                                                    deferred[ tuple[1] ](function() {
                                                            var returned = fn && fn.apply( this, arguments );
                                                            if ( returned && jQuery.isFunction( returned.promise ) ) {
                                                                    returned.promise()
                                                                            .done( newDefer.resolve )
                                                                            .fail( newDefer.reject )
                                                                            .progress( newDefer.notify );
                                                            } else {
                                                                    newDefer[ action + "With" ]( this === promise ? newDefer.promise() : this, fn ? [ returned ] : arguments );
                                                            }
                                                    });
                                            });
                                            fns = null;
                                    }).promise();
                            },
                            promise: function( obj ) {
                                    return obj != null ? jQuery.extend( obj, promise ) : promise;
                            }
                    },
                    deferred = {};

            promise.pipe = promise.then;

            jQuery.each( tuples, function( i, tuple ) {
                    var list = tuple[ 2 ],
                            stateString = tuple[ 3 ];

                    promise[ tuple[1] ] = list.add;

                    // Handle state
                    if ( stateString ) {
                            list.add(function() {
                                    state = stateString;
                            }, tuples[ i ^ 1 ][ 2 ].disable, tuples[ 2 ][ 2 ].lock );
                    }
                    deferred[ tuple[0] ] = function() {
                            deferred[ tuple[0] + "With" ]( this === deferred ? promise : this, arguments );
                            return this;
                    };
                    deferred[ tuple[0] + "With" ] = list.fireWith;
            });

            promise.promise( deferred );
            if ( func ) {
                    func.call( deferred, deferred );
            }
            return deferred;
    },

    when: function( subordinate /* , ..., subordinateN */ ) {
            var i = 0,
                    resolveValues = core_slice.call( arguments ),
                    length = resolveValues.length,
                    remaining = length !== 1 || ( subordinate && jQuery.isFunction( subordinate.promise ) ) ? length : 0,
                    deferred = remaining === 1 ? subordinate : jQuery.Deferred(),
                    updateFunc = function( i, contexts, values ) {
                            return function( value ) {
                                    contexts[ i ] = this;
                                    values[ i ] = arguments.length > 1 ? core_slice.call( arguments ) : value;
                                    if( values === progressValues ) {
                                            deferred.notifyWith( contexts, values );
                                    } else if ( !( --remaining ) ) {
                                            deferred.resolveWith( contexts, values );
                                    }
                            };
                    },
                    progressValues, progressContexts, resolveContexts;

            if ( length > 1 ) {
                    progressValues = new Array( length );
                    progressContexts = new Array( length );
                    resolveContexts = new Array( length );
                    for ( ; i < length; i++ ) {
                            if ( resolveValues[ i ] && jQuery.isFunction( resolveValues[ i ].promise ) ) {
                                    resolveValues[ i ].promise()
                                            .done( updateFunc( i, resolveContexts, resolveValues ) )
                                            .fail( deferred.reject )
                                            .progress( updateFunc( i, progressContexts, progressValues ) );
                            } else {
                                    --remaining;
                            }
                    }
            }
            if ( !remaining ) {
                    deferred.resolveWith( resolveContexts, resolveValues );
            }

            return deferred.promise();
    }
});
jQuery.support = (function( support ) {
    var input = document.createElement("input"),
            fragment = document.createDocumentFragment(),
            div = document.createElement("div"),
            select = document.createElement("select"),
            opt = select.appendChild( document.createElement("option") );

    // Finish early in limited environments
    if ( !input.type ) {
            return support;
    }

    input.type = "checkbox";

    // Support: Safari 5.1, iOS 5.1, Android 4.x, Android 2.3
    // Check the default checkbox/radio value ("" on old WebKit; "on" elsewhere)
    support.checkOn = input.value !== "";

    // Must access the parent to make an option select properly
    // Support: IE9, IE10
    support.optSelected = opt.selected;

    // Will be defined later
    support.reliableMarginRight = true;
    support.boxSizingReliable = true;
    support.pixelPosition = false;

    // Make sure checked status is properly cloned
    // Support: IE9, IE10
    input.checked = true;
    support.noCloneChecked = input.cloneNode( true ).checked;

    // Make sure that the options inside disabled selects aren't marked as disabled
    // (WebKit marks them as disabled)
    select.disabled = true;
    support.optDisabled = !opt.disabled;

    // Check if an input maintains its value after becoming a radio
    // Support: IE9, IE10
    input = document.createElement("input");
    input.value = "t";
    input.type = "radio";
    support.radioValue = input.value === "t";

    // #11217 - WebKit loses check when the name is after the checked attribute
    input.setAttribute( "checked", "t" );
    input.setAttribute( "name", "t" );

    fragment.appendChild( input );

    // Support: Safari 5.1, Android 4.x, Android 2.3
    // old WebKit doesn't clone checked state correctly in fragments
    support.checkClone = fragment.cloneNode( true ).cloneNode( true ).lastChild.checked;

    // Support: Firefox, Chrome, Safari
    // Beware of CSP restrictions (https://developer.mozilla.org/en/Security/CSP)
    support.focusinBubbles = "onfocusin" in window;

    div.style.backgroundClip = "content-box";
    div.cloneNode( true ).style.backgroundClip = "";
    support.clearCloneStyle = div.style.backgroundClip === "content-box";

    // Run tests that need a body at doc ready
    jQuery(function() {
            var container, marginDiv,
                    // Support: Firefox, Android 2.3 (Prefixed box-sizing versions).
                    divReset = "padding:0;margin:0;border:0;display:block;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box",
                    body = document.getElementsByTagName("body")[ 0 ];

            if ( !body ) {
                    // Return for frameset docs that don't have a body
                    return;
            }

            container = document.createElement("div");
            container.style.cssText = "border:0;width:0;height:0;position:absolute;top:0;left:-9999px;margin-top:1px";

            // Check box-sizing and margin behavior.
            body.appendChild( container ).appendChild( div );
            div.innerHTML = "";
            // Support: Firefox, Android 2.3 (Prefixed box-sizing versions).
            div.style.cssText = "-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%";

            // Workaround failing boxSizing test due to offsetWidth returning wrong value
            // with some non-1 values of body zoom, ticket #13543
            jQuery.swap( body, body.style.zoom != null ? { zoom: 1 } : {}, function() {
                    support.boxSizing = div.offsetWidth === 4;
            });

            // Use window.getComputedStyle because jsdom on node.js will break without it.
            if ( window.getComputedStyle ) {
                    support.pixelPosition = ( window.getComputedStyle( div, null ) || {} ).top !== "1%";
                    support.boxSizingReliable = ( window.getComputedStyle( div, null ) || { width: "4px" } ).width === "4px";

                    // Support: Android 2.3
                    // Check if div with explicit width and no margin-right incorrectly
                    // gets computed margin-right based on width of container. (#3333)
                    // WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
                    marginDiv = div.appendChild( document.createElement("div") );
                    marginDiv.style.cssText = div.style.cssText = divReset;
                    marginDiv.style.marginRight = marginDiv.style.width = "0";
                    div.style.width = "1px";

                    support.reliableMarginRight =
                            !parseFloat( ( window.getComputedStyle( marginDiv, null ) || {} ).marginRight );
            }

            body.removeChild( container );
    });

    return support;
})( {} );
var data_user, data_priv,
    rbrace = /(?:\{[\s\S]*\}|\[[\s\S]*\])$/,
    rmultiDash = /([A-Z])/g;

function Data() {
    Object.defineProperty( this.cache = {}, 0, {
            get: function() {
                    return {};
            }
    });
    this.expando = jQuery.expando + Math.random();
}

Data.uid = 1;

Data.accepts = function( owner ) {
    return owner.nodeType ?
            owner.nodeType === 1 || owner.nodeType === 9 : true;
};

Data.prototype = {
    key: function( owner ) {
            if ( !Data.accepts( owner ) ) {
                    return 0;
            }

            var descriptor = {},
                    unlock = owner[ this.expando ];

            if ( !unlock ) {
                    unlock = Data.uid++;

                    try {
                            descriptor[ this.expando ] = { value: unlock };
                            Object.defineProperties( owner, descriptor );

                    } catch ( e ) {
                            descriptor[ this.expando ] = unlock;
                            jQuery.extend( owner, descriptor );
                    }
            }
            if ( !this.cache[ unlock ] ) {
                    this.cache[ unlock ] = {};
            }
            return unlock;
    },
    set: function( owner, data, value ) {
            var prop,
                    unlock = this.key( owner ),
                    cache = this.cache[ unlock ];

            if ( typeof data === "string" ) {
                    cache[ data ] = value;

            } else {
                    if ( jQuery.isEmptyObject( cache ) ) {
                            jQuery.extend( this.cache[ unlock ], data );
                    } else {
                            for ( prop in data ) {
                                    cache[ prop ] = data[ prop ];
                            }
                    }
            }
            return cache;
    },
    get: function( owner, key ) {
            var cache = this.cache[ this.key( owner ) ];

            return key === undefined ?
                    cache : cache[ key ];
    },
    access: function( owner, key, value ) {
            var stored;
            if ( key === undefined ||
                            ((key && typeof key === "string") && value === undefined) ) {

                    stored = this.get( owner, key );

                    return stored !== undefined ?
                            stored : this.get( owner, jQuery.camelCase(key) );
            }
            this.set( owner, key, value );
            return value !== undefined ? value : key;
    },
    remove: function( owner, key ) {
            var i, name, camel,
                    unlock = this.key( owner ),
                    cache = this.cache[ unlock ];

            if ( key === undefined ) {
                    this.cache[ unlock ] = {};

            } else {
                    if ( jQuery.isArray( key ) ) {
                            name = key.concat( key.map( jQuery.camelCase ) );
                    } else {
                            camel = jQuery.camelCase( key );
                            if ( key in cache ) {
                                    name = [ key, camel ];
                            } else {
                                    name = camel;
                                    name = name in cache ?
                                            [ name ] : ( name.match( core_rnotwhite ) || [] );
                            }
                    }
                    i = name.length;
                    while ( i-- ) {
                            delete cache[ name[ i ] ];
                    }
            }
    },
    hasData: function( owner ) {
            return !jQuery.isEmptyObject(
                    this.cache[ owner[ this.expando ] ] || {}
            );
    },
    discard: function( owner ) {
            if ( owner[ this.expando ] ) {
                    delete this.cache[ owner[ this.expando ] ];
            }
    }
};

data_user = new Data();
data_priv = new Data();
jQuery.extend({
    acceptData: Data.accepts,
    hasData: function( elem ) {
            return data_user.hasData( elem ) || data_priv.hasData( elem );
    },
    data: function( elem, name, data ) {
            return data_user.access( elem, name, data );
    },
    removeData: function( elem, name ) {
            data_user.remove( elem, name );
    },
    _data: function( elem, name, data ) {
            return data_priv.access( elem, name, data );
    },

    _removeData: function( elem, name ) {
            data_priv.remove( elem, name );
    }
});

jQuery.fn.extend({
    data: function( key, value ) {
            var attrs, name,
                    elem = this[ 0 ],
                    i = 0,
                    data = null;

            // Gets all values
            if ( key === undefined ) {
                    if ( this.length ) {
                            data = data_user.get( elem );

                            if ( elem.nodeType === 1 && !data_priv.get( elem, "hasDataAttrs" ) ) {
                                    attrs = elem.attributes;
                                    for ( ; i < attrs.length; i++ ) {
                                            name = attrs[ i ].name;

                                            if ( name.indexOf( "data-" ) === 0 ) {
                                                    name = jQuery.camelCase( name.slice(5) );
                                                    dataAttr( elem, name, data[ name ] );
                                            }
                                    }
                                    data_priv.set( elem, "hasDataAttrs", true );
                            }
                    }

                    return data;
            }

            // Sets multiple values
            if ( typeof key === "object" ) {
                    return this.each(function() {
                            data_user.set( this, key );
                    });
            }

            return jQuery.access( this, function( value ) {
                    var data,
                            camelKey = jQuery.camelCase( key );

                    if ( elem && value === undefined ) {
                            data = data_user.get( elem, key );
                            if ( data !== undefined ) {
                                    return data;
                            }
                            data = data_user.get( elem, camelKey );
                            if ( data !== undefined ) {
                                    return data;
                            }
                            data = dataAttr( elem, camelKey, undefined );
                            if ( data !== undefined ) {
                                    return data;
                            }
                            return;
                    }

                    this.each(function() {
                            var data = data_user.get( this, camelKey );
                            data_user.set( this, camelKey, value );
                            if ( key.indexOf("-") !== -1 && data !== undefined ) {
                                    data_user.set( this, key, value );
                            }
                    });
            }, null, value, arguments.length > 1, null, true );
    },
    removeData: function( key ) {
            return this.each(function() {
                    data_user.remove( this, key );
            });
    }
});

function dataAttr( elem, key, data ) {
    var name;

    if ( data === undefined && elem.nodeType === 1 ) {
            name = "data-" + key.replace( rmultiDash, "-$1" ).toLowerCase();
            data = elem.getAttribute( name );

            if ( typeof data === "string" ) {
                    try {
                            data = data === "true" ? true :
                                    data === "false" ? false :
                                    data === "null" ? null :
                                    // Only convert to a number if it doesn't change the string
                                    +data + "" === data ? +data :
                                    rbrace.test( data ) ? JSON.parse( data ) :
                                    data;
                    } catch( e ) {}

                    data_user.set( elem, key, data );
            } else {
                    data = undefined;
            }
    }
    return data;
}
jQuery.extend({
    queue: function( elem, type, data ) {
            var queue;

            if ( elem ) {
                    type = ( type || "fx" ) + "queue";
                    queue = data_priv.get( elem, type );

                    // Speed up dequeue by getting out quickly if this is just a lookup
                    if ( data ) {
                            if ( !queue || jQuery.isArray( data ) ) {
                                    queue = data_priv.access( elem, type, jQuery.makeArray(data) );
                            } else {
                                    queue.push( data );
                            }
                    }
                    return queue || [];
            }
    },

    dequeue: function( elem, type ) {
            type = type || "fx";

            var queue = jQuery.queue( elem, type ),
                    startLength = queue.length,
                    fn = queue.shift(),
                    hooks = jQuery._queueHooks( elem, type ),
                    next = function() {
                            jQuery.dequeue( elem, type );
                    };

            // If the fx queue is dequeued, always remove the progress sentinel
            if ( fn === "inprogress" ) {
                    fn = queue.shift();
                    startLength--;
            }

            if ( fn ) {

                    // Add a progress sentinel to prevent the fx queue from being
                    // automatically dequeued
                    if ( type === "fx" ) {
                            queue.unshift( "inprogress" );
                    }

                    // clear up the last queue stop function
                    delete hooks.stop;
                    fn.call( elem, next, hooks );
            }

            if ( !startLength && hooks ) {
                    hooks.empty.fire();
            }
    },

    // not intended for public consumption - generates a queueHooks object, or returns the current one
    _queueHooks: function( elem, type ) {
            var key = type + "queueHooks";
            return data_priv.get( elem, key ) || data_priv.access( elem, key, {
                    empty: jQuery.Callbacks("once memory").add(function() {
                            data_priv.remove( elem, [ type + "queue", key ] );
                    })
            });
    }
});

jQuery.fn.extend({
    queue: function( type, data ) {
            var setter = 2;

            if ( typeof type !== "string" ) {
                    data = type;
                    type = "fx";
                    setter--;
            }

            if ( arguments.length < setter ) {
                    return jQuery.queue( this[0], type );
            }

            return data === undefined ?
                    this :
                    this.each(function() {
                            var queue = jQuery.queue( this, type, data );

                            // ensure a hooks for this queue
                            jQuery._queueHooks( this, type );

                            if ( type === "fx" && queue[0] !== "inprogress" ) {
                                    jQuery.dequeue( this, type );
                            }
                    });
    },
    dequeue: function( type ) {
            return this.each(function() {
                    jQuery.dequeue( this, type );
            });
    },
    // Based off of the plugin by Clint Helfers, with permission.
    // http://blindsignals.com/index.php/2009/07/jquery-delay/
    delay: function( time, type ) {
            time = jQuery.fx ? jQuery.fx.speeds[ time ] || time : time;
            type = type || "fx";

            return this.queue( type, function( next, hooks ) {
                    var timeout = setTimeout( next, time );
                    hooks.stop = function() {
                            clearTimeout( timeout );
                    };
            });
    },
    clearQueue: function( type ) {
            return this.queue( type || "fx", [] );
    },
    // Get a promise resolved when queues of a certain type
    // are emptied (fx is the type by default)
    promise: function( type, obj ) {
            var tmp,
                    count = 1,
                    defer = jQuery.Deferred(),
                    elements = this,
                    i = this.length,
                    resolve = function() {
                            if ( !( --count ) ) {
                                    defer.resolveWith( elements, [ elements ] );
                            }
                    };

            if ( typeof type !== "string" ) {
                    obj = type;
                    type = undefined;
            }
            type = type || "fx";

            while( i-- ) {
                    tmp = data_priv.get( elements[ i ], type + "queueHooks" );
                    if ( tmp && tmp.empty ) {
                            count++;
                            tmp.empty.add( resolve );
                    }
            }
            resolve();
            return defer.promise( obj );
    }
});
var nodeHook, boolHook,
    rclass = /[\t\r\n\f]/g,
    rreturn = /\r/g,
    rfocusable = /^(?:input|select|textarea|button)$/i;

jQuery.fn.extend({
    attr: function( name, value ) {
            return jQuery.access( this, jQuery.attr, name, value, arguments.length > 1 );
    },

    removeAttr: function( name ) {
            return this.each(function() {
                    jQuery.removeAttr( this, name );
            });
    },

    prop: function( name, value ) {
            return jQuery.access( this, jQuery.prop, name, value, arguments.length > 1 );
    },

    removeProp: function( name ) {
            return this.each(function() {
                    delete this[ jQuery.propFix[ name ] || name ];
            });
    },

    addClass: function( value ) {
            var classes, elem, cur, clazz, j,
                    i = 0,
                    len = this.length,
                    proceed = typeof value === "string" && value;

            if ( jQuery.isFunction( value ) ) {
                    return this.each(function( j ) {
                            jQuery( this ).addClass( value.call( this, j, this.className ) );
                    });
            }

            if ( proceed ) {
                    // The disjunction here is for better compressibility (see removeClass)
                    classes = ( value || "" ).match( core_rnotwhite ) || [];

                    for ( ; i < len; i++ ) {
                            elem = this[ i ];
                            cur = elem.nodeType === 1 && ( elem.className ?
                                    ( " " + elem.className + " " ).replace( rclass, " " ) :
                                    " "
                            );

                            if ( cur ) {
                                    j = 0;
                                    while ( (clazz = classes[j++]) ) {
                                            if ( cur.indexOf( " " + clazz + " " ) < 0 ) {
                                                    cur += clazz + " ";
                                            }
                                    }
                                    elem.className = jQuery.trim( cur );

                            }
                    }
            }

            return this;
    },

    removeClass: function( value ) {
            var classes, elem, cur, clazz, j,
                    i = 0,
                    len = this.length,
                    proceed = arguments.length === 0 || typeof value === "string" && value;

            if ( jQuery.isFunction( value ) ) {
                    return this.each(function( j ) {
                            jQuery( this ).removeClass( value.call( this, j, this.className ) );
                    });
            }
            if ( proceed ) {
                    classes = ( value || "" ).match( core_rnotwhite ) || [];

                    for ( ; i < len; i++ ) {
                            elem = this[ i ];
                            // This expression is here for better compressibility (see addClass)
                            cur = elem.nodeType === 1 && ( elem.className ?
                                    ( " " + elem.className + " " ).replace( rclass, " " ) :
                                    ""
                            );

                            if ( cur ) {
                                    j = 0;
                                    while ( (clazz = classes[j++]) ) {
                                            // Remove *all* instances
                                            while ( cur.indexOf( " " + clazz + " " ) >= 0 ) {
                                                    cur = cur.replace( " " + clazz + " ", " " );
                                            }
                                    }
                                    elem.className = value ? jQuery.trim( cur ) : "";
                            }
                    }
            }

            return this;
    },

    toggleClass: function( value, stateVal ) {
            var type = typeof value;

            if ( typeof stateVal === "boolean" && type === "string" ) {
                    return stateVal ? this.addClass( value ) : this.removeClass( value );
            }

            if ( jQuery.isFunction( value ) ) {
                    return this.each(function( i ) {
                            jQuery( this ).toggleClass( value.call(this, i, this.className, stateVal), stateVal );
                    });
            }

            return this.each(function() {
                    if ( type === "string" ) {
                            // toggle individual class names
                            var className,
                                    i = 0,
                                    self = jQuery( this ),
                                    classNames = value.match( core_rnotwhite ) || [];

                            while ( (className = classNames[ i++ ]) ) {
                                    // check each className given, space separated list
                                    if ( self.hasClass( className ) ) {
                                            self.removeClass( className );
                                    } else {
                                            self.addClass( className );
                                    }
                            }

                    // Toggle whole class name
                    } else if ( type === core_strundefined || type === "boolean" ) {
                            if ( this.className ) {
                                    // store className if set
                                    data_priv.set( this, "__className__", this.className );
                            }

                            // If the element has a class name or if we're passed "false",
                            // then remove the whole classname (if there was one, the above saved it).
                            // Otherwise bring back whatever was previously saved (if anything),
                            // falling back to the empty string if nothing was stored.
                            this.className = this.className || value === false ? "" : data_priv.get( this, "__className__" ) || "";
                    }
            });
    },

    hasClass: function( selector ) {
            var className = " " + selector + " ",
                    i = 0,
                    l = this.length;
            for ( ; i < l; i++ ) {
                    if ( this[i].nodeType === 1 && (" " + this[i].className + " ").replace(rclass, " ").indexOf( className ) >= 0 ) {
                            return true;
                    }
            }

            return false;
    },

    val: function( value ) {
            var hooks, ret, isFunction,
                    elem = this[0];

            if ( !arguments.length ) {
                    if ( elem ) {
                            hooks = jQuery.valHooks[ elem.type ] || jQuery.valHooks[ elem.nodeName.toLowerCase() ];

                            if ( hooks && "get" in hooks && (ret = hooks.get( elem, "value" )) !== undefined ) {
                                    return ret;
                            }

                            ret = elem.value;

                            return typeof ret === "string" ?
                                    // handle most common string cases
                                    ret.replace(rreturn, "") :
                                    // handle cases where value is null/undef or number
                                    ret == null ? "" : ret;
                    }

                    return;
            }

            isFunction = jQuery.isFunction( value );

            return this.each(function( i ) {
                    var val;

                    if ( this.nodeType !== 1 ) {
                            return;
                    }

                    if ( isFunction ) {
                            val = value.call( this, i, jQuery( this ).val() );
                    } else {
                            val = value;
                    }

                    // Treat null/undefined as ""; convert numbers to string
                    if ( val == null ) {
                            val = "";
                    } else if ( typeof val === "number" ) {
                            val += "";
                    } else if ( jQuery.isArray( val ) ) {
                            val = jQuery.map(val, function ( value ) {
                                    return value == null ? "" : value + "";
                            });
                    }

                    hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];

                    // If set returns undefined, fall back to normal setting
                    if ( !hooks || !("set" in hooks) || hooks.set( this, val, "value" ) === undefined ) {
                            this.value = val;
                    }
            });
    }
});

jQuery.extend({
    valHooks: {
            option: {
                    get: function( elem ) {
                            // attributes.value is undefined in Blackberry 4.7 but
                            // uses .value. See #6932
                            var val = elem.attributes.value;
                            return !val || val.specified ? elem.value : elem.text;
                    }
            },
            select: {
                    get: function( elem ) {
                            var value, option,
                                    options = elem.options,
                                    index = elem.selectedIndex,
                                    one = elem.type === "select-one" || index < 0,
                                    values = one ? null : [],
                                    max = one ? index + 1 : options.length,
                                    i = index < 0 ?
                                            max :
                                            one ? index : 0;

                            // Loop through all the selected options
                            for ( ; i < max; i++ ) {
                                    option = options[ i ];

                                    // IE6-9 doesn't update selected after form reset (#2551)
                                    if ( ( option.selected || i === index ) &&
                                                    // Don't return options that are disabled or in a disabled optgroup
                                                    ( jQuery.support.optDisabled ? !option.disabled : option.getAttribute("disabled") === null ) &&
                                                    ( !option.parentNode.disabled || !jQuery.nodeName( option.parentNode, "optgroup" ) ) ) {

                                            // Get the specific value for the option
                                            value = jQuery( option ).val();

                                            // We don't need an array for one selects
                                            if ( one ) {
                                                    return value;
                                            }

                                            // Multi-Selects return an array
                                            values.push( value );
                                    }
                            }

                            return values;
                    },

                    set: function( elem, value ) {
                            var optionSet, option,
                                    options = elem.options,
                                    values = jQuery.makeArray( value ),
                                    i = options.length;

                            while ( i-- ) {
                                    option = options[ i ];
                                    if ( (option.selected = jQuery.inArray( jQuery(option).val(), values ) >= 0) ) {
                                            optionSet = true;
                                    }
                            }

                            // force browsers to behave consistently when non-matching value is set
                            if ( !optionSet ) {
                                    elem.selectedIndex = -1;
                            }
                            return values;
                    }
            }
    },

    attr: function( elem, name, value ) {
            var hooks, ret,
                    nType = elem.nodeType;

            // don't get/set attributes on text, comment and attribute nodes
            if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
                    return;
            }

            // Fallback to prop when attributes are not supported
            if ( typeof elem.getAttribute === core_strundefined ) {
                    return jQuery.prop( elem, name, value );
            }

            // All attributes are lowercase
            // Grab necessary hook if one is defined
            if ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {
                    name = name.toLowerCase();
                    hooks = jQuery.attrHooks[ name ] ||
                            ( jQuery.expr.match.bool.test( name ) ? boolHook : nodeHook );
            }

            if ( value !== undefined ) {

                    if ( value === null ) {
                            jQuery.removeAttr( elem, name );

                    } else if ( hooks && "set" in hooks && (ret = hooks.set( elem, value, name )) !== undefined ) {
                            return ret;

                    } else {
                            elem.setAttribute( name, value + "" );
                            return value;
                    }

            } else if ( hooks && "get" in hooks && (ret = hooks.get( elem, name )) !== null ) {
                    return ret;

            } else {
                    ret = jQuery.find.attr( elem, name );

                    // Non-existent attributes return null, we normalize to undefined
                    return ret == null ?
                            undefined :
                            ret;
            }
    },

    removeAttr: function( elem, value ) {
            var name, propName,
                    i = 0,
                    attrNames = value && value.match( core_rnotwhite );

            if ( attrNames && elem.nodeType === 1 ) {
                    while ( (name = attrNames[i++]) ) {
                            propName = jQuery.propFix[ name ] || name;

                            // Boolean attributes get special treatment (#10870)
                            if ( jQuery.expr.match.bool.test( name ) ) {
                                    // Set corresponding property to false
                                    elem[ propName ] = false;
                            }

                            elem.removeAttribute( name );
                    }
            }
    },

    attrHooks: {
            type: {
                    set: function( elem, value ) {
                            if ( !jQuery.support.radioValue && value === "radio" && jQuery.nodeName(elem, "input") ) {
                                    // Setting the type on a radio button after the value resets the value in IE6-9
                                    // Reset value to default in case type is set after value during creation
                                    var val = elem.value;
                                    elem.setAttribute( "type", value );
                                    if ( val ) {
                                            elem.value = val;
                                    }
                                    return value;
                            }
                    }
            }
    },

    propFix: {
            "for": "htmlFor",
            "class": "className"
    },

    prop: function( elem, name, value ) {
            var ret, hooks, notxml,
                    nType = elem.nodeType;

            // don't get/set properties on text, comment and attribute nodes
            if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
                    return;
            }

            notxml = nType !== 1 || !jQuery.isXMLDoc( elem );

            if ( notxml ) {
                    // Fix name and attach hooks
                    name = jQuery.propFix[ name ] || name;
                    hooks = jQuery.propHooks[ name ];
            }

            if ( value !== undefined ) {
                    return hooks && "set" in hooks && (ret = hooks.set( elem, value, name )) !== undefined ?
                            ret :
                            ( elem[ name ] = value );

            } else {
                    return hooks && "get" in hooks && (ret = hooks.get( elem, name )) !== null ?
                            ret :
                            elem[ name ];
            }
    },

    propHooks: {
            tabIndex: {
                    get: function( elem ) {
                            return elem.hasAttribute( "tabindex" ) || rfocusable.test( elem.nodeName ) || elem.href ?
                                    elem.tabIndex :
                                    -1;
                    }
            }
    }
});

// Hooks for boolean attributes
boolHook = {
    set: function( elem, value, name ) {
            if ( value === false ) {
                    // Remove boolean attributes when set to false
                    jQuery.removeAttr( elem, name );
            } else {
                    elem.setAttribute( name, name );
            }
            return name;
    }
};
jQuery.each( jQuery.expr.match.bool.source.match( /\w+/g ), function( i, name ) {
    var getter = jQuery.expr.attrHandle[ name ] || jQuery.find.attr;

    jQuery.expr.attrHandle[ name ] = function( elem, name, isXML ) {
            var fn = jQuery.expr.attrHandle[ name ],
                    ret = isXML ?
                            undefined :
                            /* jshint eqeqeq: false */
                            // Temporarily disable this handler to check existence
                            (jQuery.expr.attrHandle[ name ] = undefined) !=
                                    getter( elem, name, isXML ) ?

                                    name.toLowerCase() :
                                    null;

            // Restore handler
            jQuery.expr.attrHandle[ name ] = fn;

            return ret;
    };
});

if ( !jQuery.support.optSelected ) {
    jQuery.propHooks.selected = {
            get: function( elem ) {
                    var parent = elem.parentNode;
                    if ( parent && parent.parentNode ) {
                            parent.parentNode.selectedIndex;
                    }
                    return null;
            }
    };
}

jQuery.each([
    "tabIndex",
    "readOnly",
    "maxLength",
    "cellSpacing",
    "cellPadding",
    "rowSpan",
    "colSpan",
    "useMap",
    "frameBorder",
    "contentEditable"
], function() {
    jQuery.propFix[ this.toLowerCase() ] = this;
});

jQuery.each([ "radio", "checkbox" ], function() {
    jQuery.valHooks[ this ] = {
            set: function( elem, value ) {
                    if ( jQuery.isArray( value ) ) {
                            return ( elem.checked = jQuery.inArray( jQuery(elem).val(), value ) >= 0 );
                    }
            }
    };
    if ( !jQuery.support.checkOn ) {
            jQuery.valHooks[ this ].get = function( elem ) {
                    // Support: Webkit
                    // "" is returned instead of "on" if a value isn't specified
                    return elem.getAttribute("value") === null ? "on" : elem.value;
            };
    }
});
var rkeyEvent = /^key/,
    rmouseEvent = /^(?:mouse|contextmenu)|click/,
    rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
    rtypenamespace = /^([^.]*)(?:\.(.+)|)$/;

function returnTrue() {
    return true;
}

function returnFalse() {
    return false;
}

function safeActiveElement() {
    try {
            return document.activeElement;
    } catch ( err ) { }
}
jQuery.event = {

    global: {},

    add: function( elem, types, handler, data, selector ) {

            var handleObjIn, eventHandle, tmp,
                    events, t, handleObj,
                    special, handlers, type, namespaces, origType,
                    elemData = data_priv.get( elem );

            if ( !elemData ) {
                    return;
            }

            if ( handler.handler ) {
                    handleObjIn = handler;
                    handler = handleObjIn.handler;
                    selector = handleObjIn.selector;
            }

            if ( !handler.guid ) {
                    handler.guid = jQuery.guid++;
            }

            if ( !(events = elemData.events) ) {
                    events = elemData.events = {};
            }
            if ( !(eventHandle = elemData.handle) ) {
                    eventHandle = elemData.handle = function( e ) {
                            return typeof jQuery !== core_strundefined && (!e || jQuery.event.triggered !== e.type) ?
                                    jQuery.event.dispatch.apply( eventHandle.elem, arguments ) :
                                    undefined;
                    };
                    eventHandle.elem = elem;
            }
            types = ( types || "" ).match( core_rnotwhite ) || [""];
            t = types.length;
            while ( t-- ) {
                    tmp = rtypenamespace.exec( types[t] ) || [];
                    type = origType = tmp[1];
                    namespaces = ( tmp[2] || "" ).split( "." ).sort();

                    if ( !type ) {
                            continue;
                    }

                    special = jQuery.event.special[ type ] || {};
                    type = ( selector ? special.delegateType : special.bindType ) || type;
                    special = jQuery.event.special[ type ] || {};
                    handleObj = jQuery.extend({
                            type: type,
                            origType: origType,
                            data: data,
                            handler: handler,
                            guid: handler.guid,
                            selector: selector,
                            needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
                            namespace: namespaces.join(".")
                    }, handleObjIn );

                    if ( !(handlers = events[ type ]) ) {
                            handlers = events[ type ] = [];
                            handlers.delegateCount = 0;

                            // Only use addEventListener if the special events handler returns false
                            if ( !special.setup || special.setup.call( elem, data, namespaces, eventHandle ) === false ) {
                                    if ( elem.addEventListener ) {
                                            elem.addEventListener( type, eventHandle, false );
                                    }
                            }
                    }

                    if ( special.add ) {
                            special.add.call( elem, handleObj );

                            if ( !handleObj.handler.guid ) {
                                    handleObj.handler.guid = handler.guid;
                            }
                    }

                    // Add to the element's handler list, delegates in front
                    if ( selector ) {
                            handlers.splice( handlers.delegateCount++, 0, handleObj );
                    } else {
                            handlers.push( handleObj );
                    }

                    // Keep track of which events have ever been used, for event optimization
                    jQuery.event.global[ type ] = true;
            }

            // Nullify elem to prevent memory leaks in IE
            elem = null;
    },

    // Detach an event or set of events from an element
    remove: function( elem, types, handler, selector, mappedTypes ) {

            var j, origCount, tmp,
                    events, t, handleObj,
                    special, handlers, type, namespaces, origType,
                    elemData = data_priv.hasData( elem ) && data_priv.get( elem );

            if ( !elemData || !(events = elemData.events) ) {
                    return;
            }

            // Once for each type.namespace in types; type may be omitted
            types = ( types || "" ).match( core_rnotwhite ) || [""];
            t = types.length;
            while ( t-- ) {
                    tmp = rtypenamespace.exec( types[t] ) || [];
                    type = origType = tmp[1];
                    namespaces = ( tmp[2] || "" ).split( "." ).sort();

                    // Unbind all events (on this namespace, if provided) for the element
                    if ( !type ) {
                            for ( type in events ) {
                                    jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
                            }
                            continue;
                    }

                    special = jQuery.event.special[ type ] || {};
                    type = ( selector ? special.delegateType : special.bindType ) || type;
                    handlers = events[ type ] || [];
                    tmp = tmp[2] && new RegExp( "(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)" );

                    // Remove matching events
                    origCount = j = handlers.length;
                    while ( j-- ) {
                            handleObj = handlers[ j ];

                            if ( ( mappedTypes || origType === handleObj.origType ) &&
                                    ( !handler || handler.guid === handleObj.guid ) &&
                                    ( !tmp || tmp.test( handleObj.namespace ) ) &&
                                    ( !selector || selector === handleObj.selector || selector === "**" && handleObj.selector ) ) {
                                    handlers.splice( j, 1 );

                                    if ( handleObj.selector ) {
                                            handlers.delegateCount--;
                                    }
                                    if ( special.remove ) {
                                            special.remove.call( elem, handleObj );
                                    }
                            }
                    }

                    // Remove generic event handler if we removed something and no more handlers exist
                    // (avoids potential for endless recursion during removal of special event handlers)
                    if ( origCount && !handlers.length ) {
                            if ( !special.teardown || special.teardown.call( elem, namespaces, elemData.handle ) === false ) {
                                    jQuery.removeEvent( elem, type, elemData.handle );
                            }

                            delete events[ type ];
                    }
            }

            // Remove the expando if it's no longer used
            if ( jQuery.isEmptyObject( events ) ) {
                    delete elemData.handle;
                    data_priv.remove( elem, "events" );
            }
    },

    trigger: function( event, data, elem, onlyHandlers ) {

            var i, cur, tmp, bubbleType, ontype, handle, special,
                    eventPath = [ elem || document ],
                    type = core_hasOwn.call( event, "type" ) ? event.type : event,
                    namespaces = core_hasOwn.call( event, "namespace" ) ? event.namespace.split(".") : [];

            cur = tmp = elem = elem || document;

            // Don't do events on text and comment nodes
            if ( elem.nodeType === 3 || elem.nodeType === 8 ) {
                    return;
            }

            // focus/blur morphs to focusin/out; ensure we're not firing them right now
            if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
                    return;
            }

            if ( type.indexOf(".") >= 0 ) {
                    // Namespaced trigger; create a regexp to match event type in handle()
                    namespaces = type.split(".");
                    type = namespaces.shift();
                    namespaces.sort();
            }
            ontype = type.indexOf(":") < 0 && "on" + type;

            // Caller can pass in a jQuery.Event object, Object, or just an event type string
            event = event[ jQuery.expando ] ?
                    event :
                    new jQuery.Event( type, typeof event === "object" && event );

            // Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
            event.isTrigger = onlyHandlers ? 2 : 3;
            event.namespace = namespaces.join(".");
            event.namespace_re = event.namespace ?
                    new RegExp( "(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)" ) :
                    null;

            // Clean up the event in case it is being reused
            event.result = undefined;
            if ( !event.target ) {
                    event.target = elem;
            }

            // Clone any incoming data and prepend the event, creating the handler arg list
            data = data == null ?
                    [ event ] :
                    jQuery.makeArray( data, [ event ] );

            // Allow special events to draw outside the lines
            special = jQuery.event.special[ type ] || {};
            if ( !onlyHandlers && special.trigger && special.trigger.apply( elem, data ) === false ) {
                    return;
            }

            // Determine event propagation path in advance, per W3C events spec (#9951)
            // Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
            if ( !onlyHandlers && !special.noBubble && !jQuery.isWindow( elem ) ) {

                    bubbleType = special.delegateType || type;
                    if ( !rfocusMorph.test( bubbleType + type ) ) {
                            cur = cur.parentNode;
                    }
                    for ( ; cur; cur = cur.parentNode ) {
                            eventPath.push( cur );
                            tmp = cur;
                    }

                    // Only add window if we got to document (e.g., not plain obj or detached DOM)
                    if ( tmp === (elem.ownerDocument || document) ) {
                            eventPath.push( tmp.defaultView || tmp.parentWindow || window );
                    }
            }

            // Fire handlers on the event path
            i = 0;
            while ( (cur = eventPath[i++]) && !event.isPropagationStopped() ) {

                    event.type = i > 1 ?
                            bubbleType :
                            special.bindType || type;

                    // jQuery handler
                    handle = ( data_priv.get( cur, "events" ) || {} )[ event.type ] && data_priv.get( cur, "handle" );
                    if ( handle ) {
                            handle.apply( cur, data );
                    }

                    // Native handler
                    handle = ontype && cur[ ontype ];
                    if ( handle && jQuery.acceptData( cur ) && handle.apply && handle.apply( cur, data ) === false ) {
                            event.preventDefault();
                    }
            }
            event.type = type;

            // If nobody prevented the default action, do it now
            if ( !onlyHandlers && !event.isDefaultPrevented() ) {

                    if ( (!special._default || special._default.apply( eventPath.pop(), data ) === false) &&
                            jQuery.acceptData( elem ) ) {

                            // Call a native DOM method on the target with the same name name as the event.
                            // Don't do default actions on window, that's where global variables be (#6170)
                            if ( ontype && jQuery.isFunction( elem[ type ] ) && !jQuery.isWindow( elem ) ) {

                                    // Don't re-trigger an onFOO event when we call its FOO() method
                                    tmp = elem[ ontype ];

                                    if ( tmp ) {
                                            elem[ ontype ] = null;
                                    }

                                    // Prevent re-triggering of the same event, since we already bubbled it above
                                    jQuery.event.triggered = type;
                                    elem[ type ]();
                                    jQuery.event.triggered = undefined;

                                    if ( tmp ) {
                                            elem[ ontype ] = tmp;
                                    }
                            }
                    }
            }

            return event.result;
    },

    dispatch: function( event ) {

            // Make a writable jQuery.Event from the native event object
            event = jQuery.event.fix( event );

            var i, j, ret, matched, handleObj,
                    handlerQueue = [],
                    args = core_slice.call( arguments ),
                    handlers = ( data_priv.get( this, "events" ) || {} )[ event.type ] || [],
                    special = jQuery.event.special[ event.type ] || {};

            // Use the fix-ed jQuery.Event rather than the (read-only) native event
            args[0] = event;
            event.delegateTarget = this;

            // Call the preDispatch hook for the mapped type, and let it bail if desired
            if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
                    return;
            }

            // Determine handlers
            handlerQueue = jQuery.event.handlers.call( this, event, handlers );

            // Run delegates first; they may want to stop propagation beneath us
            i = 0;
            while ( (matched = handlerQueue[ i++ ]) && !event.isPropagationStopped() ) {
                    event.currentTarget = matched.elem;

                    j = 0;
                    while ( (handleObj = matched.handlers[ j++ ]) && !event.isImmediatePropagationStopped() ) {

                            // Triggered event must either 1) have no namespace, or
                            // 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
                            if ( !event.namespace_re || event.namespace_re.test( handleObj.namespace ) ) {

                                    event.handleObj = handleObj;
                                    event.data = handleObj.data;

                                    ret = ( (jQuery.event.special[ handleObj.origType ] || {}).handle || handleObj.handler )
                                                    .apply( matched.elem, args );

                                    if ( ret !== undefined ) {
                                            if ( (event.result = ret) === false ) {
                                                    event.preventDefault();
                                                    event.stopPropagation();
                                            }
                                    }
                            }
                    }
            }

            // Call the postDispatch hook for the mapped type
            if ( special.postDispatch ) {
                    special.postDispatch.call( this, event );
            }

            return event.result;
    },

    handlers: function( event, handlers ) {
            var i, matches, sel, handleObj,
                    handlerQueue = [],
                    delegateCount = handlers.delegateCount,
                    cur = event.target;

            // Find delegate handlers
            // Black-hole SVG <use> instance trees (#13180)
            // Avoid non-left-click bubbling in Firefox (#3861)
            if ( delegateCount && cur.nodeType && (!event.button || event.type !== "click") ) {

                    for ( ; cur !== this; cur = cur.parentNode || this ) {

                            // Don't process clicks on disabled elements (#6911, #8165, #11382, #11764)
                            if ( cur.disabled !== true || event.type !== "click" ) {
                                    matches = [];
                                    for ( i = 0; i < delegateCount; i++ ) {
                                            handleObj = handlers[ i ];

                                            // Don't conflict with Object.prototype properties (#13203)
                                            sel = handleObj.selector + " ";

                                            if ( matches[ sel ] === undefined ) {
                                                    matches[ sel ] = handleObj.needsContext ?
                                                            jQuery( sel, this ).index( cur ) >= 0 :
                                                            jQuery.find( sel, this, null, [ cur ] ).length;
                                            }
                                            if ( matches[ sel ] ) {
                                                    matches.push( handleObj );
                                            }
                                    }
                                    if ( matches.length ) {
                                            handlerQueue.push({ elem: cur, handlers: matches });
                                    }
                            }
                    }
            }

            // Add the remaining (directly-bound) handlers
            if ( delegateCount < handlers.length ) {
                    handlerQueue.push({ elem: this, handlers: handlers.slice( delegateCount ) });
            }

            return handlerQueue;
    },

    // Includes some event props shared by KeyEvent and MouseEvent
    props: "altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),

    fixHooks: {},

    keyHooks: {
            props: "char charCode key keyCode".split(" "),
            filter: function( event, original ) {

                    // Add which for key events
                    if ( event.which == null ) {
                            event.which = original.charCode != null ? original.charCode : original.keyCode;
                    }

                    return event;
            }
    },

    mouseHooks: {
            props: "button buttons clientX clientY offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
            filter: function( event, original ) {
                    var eventDoc, doc, body,
                            button = original.button;

                    // Calculate pageX/Y if missing and clientX/Y available
                    if ( event.pageX == null && original.clientX != null ) {
                            eventDoc = event.target.ownerDocument || document;
                            doc = eventDoc.documentElement;
                            body = eventDoc.body;

                            event.pageX = original.clientX + ( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) - ( doc && doc.clientLeft || body && body.clientLeft || 0 );
                            event.pageY = original.clientY + ( doc && doc.scrollTop  || body && body.scrollTop  || 0 ) - ( doc && doc.clientTop  || body && body.clientTop  || 0 );
                    }

                    // Add which for click: 1 === left; 2 === middle; 3 === right
                    // Note: button is not normalized, so don't use it
                    if ( !event.which && button !== undefined ) {
                            event.which = ( button & 1 ? 1 : ( button & 2 ? 3 : ( button & 4 ? 2 : 0 ) ) );
                    }

                    return event;
            }
    },

    fix: function( event ) {
            if ( event[ jQuery.expando ] ) {
                    return event;
            }

            // Create a writable copy of the event object and normalize some properties
            var i, prop, copy,
                    type = event.type,
                    originalEvent = event,
                    fixHook = this.fixHooks[ type ];

            if ( !fixHook ) {
                    this.fixHooks[ type ] = fixHook =
                            rmouseEvent.test( type ) ? this.mouseHooks :
                            rkeyEvent.test( type ) ? this.keyHooks :
                            {};
            }
            copy = fixHook.props ? this.props.concat( fixHook.props ) : this.props;

            event = new jQuery.Event( originalEvent );

            i = copy.length;
            while ( i-- ) {
                    prop = copy[ i ];
                    event[ prop ] = originalEvent[ prop ];
            }

            // Support: Cordova 2.5 (WebKit) (#13255)
            // All events should have a target; Cordova deviceready doesn't
            if ( !event.target ) {
                    event.target = document;
            }

            // Support: Safari 6.0+, Chrome < 28
            // Target should not be a text node (#504, #13143)
            if ( event.target.nodeType === 3 ) {
                    event.target = event.target.parentNode;
            }

            return fixHook.filter? fixHook.filter( event, originalEvent ) : event;
    },

    special: {
            load: {
                    // Prevent triggered image.load events from bubbling to window.load
                    noBubble: true
            },
            focus: {
                    // Fire native event if possible so blur/focus sequence is correct
                    trigger: function() {
                            if ( this !== safeActiveElement() && this.focus ) {
                                    this.focus();
                                    return false;
                            }
                    },
                    delegateType: "focusin"
            },
            blur: {
                    trigger: function() {
                            if ( this === safeActiveElement() && this.blur ) {
                                    this.blur();
                                    return false;
                            }
                    },
                    delegateType: "focusout"
            },
            click: {
                    // For checkbox, fire native event so checked state will be right
                    trigger: function() {
                            if ( this.type === "checkbox" && this.click && jQuery.nodeName( this, "input" ) ) {
                                    this.click();
                                    return false;
                            }
                    },

                    // For cross-browser consistency, don't fire native .click() on links
                    _default: function( event ) {
                            return jQuery.nodeName( event.target, "a" );
                    }
            },

            beforeunload: {
                    postDispatch: function( event ) {

                            // Support: Firefox 20+
                            // Firefox doesn't alert if the returnValue field is not set.
                            if ( event.result !== undefined ) {
                                    event.originalEvent.returnValue = event.result;
                            }
                    }
            }
    },

    simulate: function( type, elem, event, bubble ) {
            // Piggyback on a donor event to simulate a different one.
            // Fake originalEvent to avoid donor's stopPropagation, but if the
            // simulated event prevents default then we do the same on the donor.
            var e = jQuery.extend(
                    new jQuery.Event(),
                    event,
                    {
                            type: type,
                            isSimulated: true,
                            originalEvent: {}
                    }
            );
            if ( bubble ) {
                    jQuery.event.trigger( e, null, elem );
            } else {
                    jQuery.event.dispatch.call( elem, e );
            }
            if ( e.isDefaultPrevented() ) {
                    event.preventDefault();
            }
    }
};

jQuery.removeEvent = function( elem, type, handle ) {
    if ( elem.removeEventListener ) {
            elem.removeEventListener( type, handle, false );
    }
};

jQuery.Event = function( src, props ) {
    // Allow instantiation without the 'new' keyword
    if ( !(this instanceof jQuery.Event) ) {
            return new jQuery.Event( src, props );
    }

    // Event object
    if ( src && src.type ) {
            this.originalEvent = src;
            this.type = src.type;

            // Events bubbling up the document may have been marked as prevented
            // by a handler lower down the tree; reflect the correct value.
            this.isDefaultPrevented = ( src.defaultPrevented || src.defaultPrevented && src.defaultPrevented() ) ? returnTrue : returnFalse;

    // Event type
    } else {
            this.type = src;
    }

    // Put explicitly provided properties onto the event object
    if ( props ) {
            jQuery.extend( this, props );
    }

    // Create a timestamp if incoming event doesn't have one
    this.timeStamp = src && src.timeStamp || jQuery.now();

    // Mark it as fixed
    this[ jQuery.expando ] = true;
};

jQuery.Event.prototype = {
    isDefaultPrevented: returnFalse,
    isPropagationStopped: returnFalse,
    isImmediatePropagationStopped: returnFalse,

    preventDefault: function() {
            var e = this.originalEvent;

            this.isDefaultPrevented = returnTrue;

            if ( e && e.preventDefault ) {
                    e.preventDefault();
            }
    },
    stopPropagation: function() {
            var e = this.originalEvent;

            this.isPropagationStopped = returnTrue;

            if ( e && e.stopPropagation ) {
                    e.stopPropagation();
            }
    },
    stopImmediatePropagation: function() {
            this.isImmediatePropagationStopped = returnTrue;
            this.stopPropagation();
    }
};

// Create mouseenter/leave events using mouseover/out and event-time checks
// Support: Chrome 15+
jQuery.each({
    mouseenter: "mouseover",
    mouseleave: "mouseout"
}, function( orig, fix ) {
    jQuery.event.special[ orig ] = {
            delegateType: fix,
            bindType: fix,

            handle: function( event ) {
                    var ret,
                            target = this,
                            related = event.relatedTarget,
                            handleObj = event.handleObj;

                    // For mousenter/leave call the handler if related is outside the target.
                    // NB: No relatedTarget if the mouse left/entered the browser window
                    if ( !related || (related !== target && !jQuery.contains( target, related )) ) {
                            event.type = handleObj.origType;
                            ret = handleObj.handler.apply( this, arguments );
                            event.type = fix;
                    }
                    return ret;
            }
    };
});

// Create "bubbling" focus and blur events
// Support: Firefox, Chrome, Safari
if ( !jQuery.support.focusinBubbles ) {
    jQuery.each({ focus: "focusin", blur: "focusout" }, function( orig, fix ) {

            // Attach a single capturing handler while someone wants focusin/focusout
            var attaches = 0,
                    handler = function( event ) {
                            jQuery.event.simulate( fix, event.target, jQuery.event.fix( event ), true );
                    };

            jQuery.event.special[ fix ] = {
                    setup: function() {
                            if ( attaches++ === 0 ) {
                                    document.addEventListener( orig, handler, true );
                            }
                    },
                    teardown: function() {
                            if ( --attaches === 0 ) {
                                    document.removeEventListener( orig, handler, true );
                            }
                    }
            };
    });
}

jQuery.fn.extend({

    on: function( types, selector, data, fn, /*INTERNAL*/ one ) {
            var origFn, type;

            // Types can be a map of types/handlers
            if ( typeof types === "object" ) {
                    // ( types-Object, selector, data )
                    if ( typeof selector !== "string" ) {
                            // ( types-Object, data )
                            data = data || selector;
                            selector = undefined;
                    }
                    for ( type in types ) {
                            this.on( type, selector, data, types[ type ], one );
                    }
                    return this;
            }

            if ( data == null && fn == null ) {
                    // ( types, fn )
                    fn = selector;
                    data = selector = undefined;
            } else if ( fn == null ) {
                    if ( typeof selector === "string" ) {
                            // ( types, selector, fn )
                            fn = data;
                            data = undefined;
                    } else {
                            // ( types, data, fn )
                            fn = data;
                            data = selector;
                            selector = undefined;
                    }
            }
            if ( fn === false ) {
                    fn = returnFalse;
            } else if ( !fn ) {
                    return this;
            }

            if ( one === 1 ) {
                    origFn = fn;
                    fn = function( event ) {
                            // Can use an empty set, since event contains the info
                            jQuery().off( event );
                            return origFn.apply( this, arguments );
                    };
                    // Use same guid so caller can remove using origFn
                    fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
            }
            return this.each( function() {
                    jQuery.event.add( this, types, fn, data, selector );
            });
    },
    one: function( types, selector, data, fn ) {
            return this.on( types, selector, data, fn, 1 );
    },
    off: function( types, selector, fn ) {
            var handleObj, type;
            if ( types && types.preventDefault && types.handleObj ) {
                    // ( event )  dispatched jQuery.Event
                    handleObj = types.handleObj;
                    jQuery( types.delegateTarget ).off(
                            handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType,
                            handleObj.selector,
                            handleObj.handler
                    );
                    return this;
            }
            if ( typeof types === "object" ) {
                    // ( types-object [, selector] )
                    for ( type in types ) {
                            this.off( type, selector, types[ type ] );
                    }
                    return this;
            }
            if ( selector === false || typeof selector === "function" ) {
                    // ( types [, fn] )
                    fn = selector;
                    selector = undefined;
            }
            if ( fn === false ) {
                    fn = returnFalse;
            }
            return this.each(function() {
                    jQuery.event.remove( this, types, fn, selector );
            });
    },

    trigger: function( type, data ) {
            return this.each(function() {
                    jQuery.event.trigger( type, data, this );
            });
    },
    triggerHandler: function( type, data ) {
            var elem = this[0];
            if ( elem ) {
                    return jQuery.event.trigger( type, data, elem, true );
            }
    }
});
var isSimple = /^.[^:#\[\.,]*$/,
    rparentsprev = /^(?:parents|prev(?:Until|All))/,
    rneedsContext = jQuery.expr.match.needsContext,
    // methods guaranteed to produce a unique set when starting from a unique set
    guaranteedUnique = {
            children: true,
            contents: true,
            next: true,
            prev: true
    };

jQuery.fn.extend({
    find: function( selector ) {
            var i,
                    ret = [],
                    self = this,
                    len = self.length;

            if ( typeof selector !== "string" ) {
                    return this.pushStack( jQuery( selector ).filter(function() {
                            for ( i = 0; i < len; i++ ) {
                                    if ( jQuery.contains( self[ i ], this ) ) {
                                            return true;
                                    }
                            }
                    }) );
            }

            for ( i = 0; i < len; i++ ) {
                    jQuery.find( selector, self[ i ], ret );
            }

            // Needed because $( selector, context ) becomes $( context ).find( selector )
            ret = this.pushStack( len > 1 ? jQuery.unique( ret ) : ret );
            ret.selector = this.selector ? this.selector + " " + selector : selector;
            return ret;
    },

    has: function( target ) {
            var targets = jQuery( target, this ),
                    l = targets.length;

            return this.filter(function() {
                    var i = 0;
                    for ( ; i < l; i++ ) {
                            if ( jQuery.contains( this, targets[i] ) ) {
                                    return true;
                            }
                    }
            });
    },

    not: function( selector ) {
            return this.pushStack( winnow(this, selector || [], true) );
    },

    filter: function( selector ) {
            return this.pushStack( winnow(this, selector || [], false) );
    },

    is: function( selector ) {
            return !!winnow(
                    this,

                    // If this is a positional/relative selector, check membership in the returned set
                    // so $("p:first").is("p:last") won't return true for a doc with two "p".
                    typeof selector === "string" && rneedsContext.test( selector ) ?
                            jQuery( selector ) :
                            selector || [],
                    false
            ).length;
    },

    closest: function( selectors, context ) {
            var cur,
                    i = 0,
                    l = this.length,
                    matched = [],
                    pos = ( rneedsContext.test( selectors ) || typeof selectors !== "string" ) ?
                            jQuery( selectors, context || this.context ) :
                            0;

            for ( ; i < l; i++ ) {
                    for ( cur = this[i]; cur && cur !== context; cur = cur.parentNode ) {
                            // Always skip document fragments
                            if ( cur.nodeType < 11 && (pos ?
                                    pos.index(cur) > -1 :

                                    // Don't pass non-elements to Sizzle
                                    cur.nodeType === 1 &&
                                            jQuery.find.matchesSelector(cur, selectors)) ) {

                                    cur = matched.push( cur );
                                    break;
                            }
                    }
            }

            return this.pushStack( matched.length > 1 ? jQuery.unique( matched ) : matched );
    },

    // Determine the position of an element within
    // the matched set of elements
    index: function( elem ) {

            // No argument, return index in parent
            if ( !elem ) {
                    return ( this[ 0 ] && this[ 0 ].parentNode ) ? this.first().prevAll().length : -1;
            }

            // index in selector
            if ( typeof elem === "string" ) {
                    return core_indexOf.call( jQuery( elem ), this[ 0 ] );
            }

            // Locate the position of the desired element
            return core_indexOf.call( this,

                    // If it receives a jQuery object, the first element is used
                    elem.jquery ? elem[ 0 ] : elem
            );
    },

    add: function( selector, context ) {
            var set = typeof selector === "string" ?
                            jQuery( selector, context ) :
                            jQuery.makeArray( selector && selector.nodeType ? [ selector ] : selector ),
                    all = jQuery.merge( this.get(), set );

            return this.pushStack( jQuery.unique(all) );
    },

    addBack: function( selector ) {
            return this.add( selector == null ?
                    this.prevObject : this.prevObject.filter(selector)
            );
    }
});

function sibling( cur, dir ) {
    while ( (cur = cur[dir]) && cur.nodeType !== 1 ) {}

    return cur;
}

jQuery.each({
    parent: function( elem ) {
            var parent = elem.parentNode;
            return parent && parent.nodeType !== 11 ? parent : null;
    },
    parents: function( elem ) {
            return jQuery.dir( elem, "parentNode" );
    },
    parentsUntil: function( elem, i, until ) {
            return jQuery.dir( elem, "parentNode", until );
    },
    next: function( elem ) {
            return sibling( elem, "nextSibling" );
    },
    prev: function( elem ) {
            return sibling( elem, "previousSibling" );
    },
    nextAll: function( elem ) {
            return jQuery.dir( elem, "nextSibling" );
    },
    prevAll: function( elem ) {
            return jQuery.dir( elem, "previousSibling" );
    },
    nextUntil: function( elem, i, until ) {
            return jQuery.dir( elem, "nextSibling", until );
    },
    prevUntil: function( elem, i, until ) {
            return jQuery.dir( elem, "previousSibling", until );
    },
    siblings: function( elem ) {
            return jQuery.sibling( ( elem.parentNode || {} ).firstChild, elem );
    },
    children: function( elem ) {
            return jQuery.sibling( elem.firstChild );
    },
    contents: function( elem ) {
            return elem.contentDocument || jQuery.merge( [], elem.childNodes );
    }
}, function( name, fn ) {
    jQuery.fn[ name ] = function( until, selector ) {
            var matched = jQuery.map( this, fn, until );

            if ( name.slice( -5 ) !== "Until" ) {
                    selector = until;
            }

            if ( selector && typeof selector === "string" ) {
                    matched = jQuery.filter( selector, matched );
            }

            if ( this.length > 1 ) {
                    // Remove duplicates
                    if ( !guaranteedUnique[ name ] ) {
                            jQuery.unique( matched );
                    }

                    // Reverse order for parents* and prev-derivatives
                    if ( rparentsprev.test( name ) ) {
                            matched.reverse();
                    }
            }

            return this.pushStack( matched );
    };
});

jQuery.extend({
    filter: function( expr, elems, not ) {
            var elem = elems[ 0 ];

            if ( not ) {
                    expr = ":not(" + expr + ")";
            }

            return elems.length === 1 && elem.nodeType === 1 ?
                    jQuery.find.matchesSelector( elem, expr ) ? [ elem ] : [] :
                    jQuery.find.matches( expr, jQuery.grep( elems, function( elem ) {
                            return elem.nodeType === 1;
                    }));
    },

    dir: function( elem, dir, until ) {
            var matched = [],
                    truncate = until !== undefined;

            while ( (elem = elem[ dir ]) && elem.nodeType !== 9 ) {
                    if ( elem.nodeType === 1 ) {
                            if ( truncate && jQuery( elem ).is( until ) ) {
                                    break;
                            }
                            matched.push( elem );
                    }
            }
            return matched;
    },

    sibling: function( n, elem ) {
            var matched = [];

            for ( ; n; n = n.nextSibling ) {
                    if ( n.nodeType === 1 && n !== elem ) {
                            matched.push( n );
                    }
            }

            return matched;
    }
});

function winnow( elements, qualifier, not ) {
    if ( jQuery.isFunction( qualifier ) ) {
            return jQuery.grep( elements, function( elem, i ) {
                    /* jshint -W018 */
                    return !!qualifier.call( elem, i, elem ) !== not;
            });

    }

    if ( qualifier.nodeType ) {
            return jQuery.grep( elements, function( elem ) {
                    return ( elem === qualifier ) !== not;
            });

    }

    if ( typeof qualifier === "string" ) {
            if ( isSimple.test( qualifier ) ) {
                    return jQuery.filter( qualifier, elements, not );
            }

            qualifier = jQuery.filter( qualifier, elements );
    }

    return jQuery.grep( elements, function( elem ) {
            return ( core_indexOf.call( qualifier, elem ) >= 0 ) !== not;
    });
}
var rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
    rtagName = /<([\w:]+)/,
    rhtml = /<|&#?\w+;/,
    rnoInnerhtml = /<(?:script|style|link)/i,
    manipulation_rcheckableType = /^(?:checkbox|radio)$/i,
    rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,
    rscriptType = /^$|\/(?:java|ecma)script/i,
    rscriptTypeMasked = /^true\/(.*)/,
    rcleanScript = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,

    wrapMap = {
            option: [ 1, "<select multiple='multiple'>", "</select>" ],
            thead: [ 1, "<table>", "</table>" ],
            col: [ 2, "<table><colgroup>", "</colgroup></table>" ],
            tr: [ 2, "<table><tbody>", "</tbody></table>" ],
            td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],
            _default: [ 0, "", "" ]
    };

wrapMap.optgroup = wrapMap.option;
wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;

jQuery.fn.extend({
    text: function( value ) {
            return jQuery.access( this, function( value ) {
                    return value === undefined ?
                            jQuery.text( this ) :
                            this.empty().append( ( this[ 0 ] && this[ 0 ].ownerDocument || document ).createTextNode( value ) );
            }, null, value, arguments.length );
    },
    append: function() {
            return this.domManip( arguments, function( elem ) {
                    if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
                            var target = manipulationTarget( this, elem );
                            target.appendChild( elem );
                    }
            });
    },
    prepend: function() {
            return this.domManip( arguments, function( elem ) {
                    if ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
                            var target = manipulationTarget( this, elem );
                            target.insertBefore( elem, target.firstChild );
                    }
            });
    },
    before: function() {
            return this.domManip( arguments, function( elem ) {
                    if ( this.parentNode ) {
                            this.parentNode.insertBefore( elem, this );
                    }
            });
    },

    after: function() {
            return this.domManip( arguments, function( elem ) {
                    if ( this.parentNode ) {
                            this.parentNode.insertBefore( elem, this.nextSibling );
                    }
            });
    },

    // keepData is for internal use only--do not document
    remove: function( selector, keepData ) {
            var elem,
                    elems = selector ? jQuery.filter( selector, this ) : this,
                    i = 0;

            for ( ; (elem = elems[i]) != null; i++ ) {
                    if ( !keepData && elem.nodeType === 1 ) {
                            jQuery.cleanData( getAll( elem ) );
                    }

                    if ( elem.parentNode ) {
                            if ( keepData && jQuery.contains( elem.ownerDocument, elem ) ) {
                                    setGlobalEval( getAll( elem, "script" ) );
                            }
                            elem.parentNode.removeChild( elem );
                    }
            }

            return this;
    },

    empty: function() {
            var elem,
                    i = 0;

            for ( ; (elem = this[i]) != null; i++ ) {
                    if ( elem.nodeType === 1 ) {

                            // Prevent memory leaks
                            jQuery.cleanData( getAll( elem, false ) );

                            // Remove any remaining nodes
                            elem.textContent = "";
                    }
            }

            return this;
    },

    clone: function( dataAndEvents, deepDataAndEvents ) {
            dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
            deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;

            return this.map( function () {
                    return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
            });
    },

    html: function( value ) {
            return jQuery.access( this, function( value ) {
                    var elem = this[ 0 ] || {},
                            i = 0,
                            l = this.length;

                    if ( value === undefined && elem.nodeType === 1 ) {
                            return elem.innerHTML;
                    }

                    // See if we can take a shortcut and just use innerHTML
                    if ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
                            !wrapMap[ ( rtagName.exec( value ) || [ "", "" ] )[ 1 ].toLowerCase() ] ) {

                            value = value.replace( rxhtmlTag, "<$1></$2>" );

                            try {
                                    for ( ; i < l; i++ ) {
                                            elem = this[ i ] || {};

                                            // Remove element nodes and prevent memory leaks
                                            if ( elem.nodeType === 1 ) {
                                                    jQuery.cleanData( getAll( elem, false ) );
                                                    elem.innerHTML = value;
                                            }
                                    }

                                    elem = 0;

                            // If using innerHTML throws an exception, use the fallback method
                            } catch( e ) {}
                    }

                    if ( elem ) {
                            this.empty().append( value );
                    }
            }, null, value, arguments.length );
    },

    replaceWith: function() {
            var
                    // Snapshot the DOM in case .domManip sweeps something relevant into its fragment
                    args = jQuery.map( this, function( elem ) {
                            return [ elem.nextSibling, elem.parentNode ];
                    }),
                    i = 0;

            // Make the changes, replacing each context element with the new content
            this.domManip( arguments, function( elem ) {
                    var next = args[ i++ ],
                            parent = args[ i++ ];

                    if ( parent ) {
                            // Don't use the snapshot next if it has moved (#13810)
                            if ( next && next.parentNode !== parent ) {
                                    next = this.nextSibling;
                            }
                            jQuery( this ).remove();
                            parent.insertBefore( elem, next );
                    }
            // Allow new content to include elements from the context set
            }, true );

            // Force removal if there was no new content (e.g., from empty arguments)
            return i ? this : this.remove();
    },

    detach: function( selector ) {
            return this.remove( selector, true );
    },

    domManip: function( args, callback, allowIntersection ) {

            // Flatten any nested arrays
            args = core_concat.apply( [], args );

            var fragment, first, scripts, hasScripts, node, doc,
                    i = 0,
                    l = this.length,
                    set = this,
                    iNoClone = l - 1,
                    value = args[ 0 ],
                    isFunction = jQuery.isFunction( value );

            // We can't cloneNode fragments that contain checked, in WebKit
            if ( isFunction || !( l <= 1 || typeof value !== "string" || jQuery.support.checkClone || !rchecked.test( value ) ) ) {
                    return this.each(function( index ) {
                            var self = set.eq( index );
                            if ( isFunction ) {
                                    args[ 0 ] = value.call( this, index, self.html() );
                            }
                            self.domManip( args, callback, allowIntersection );
                    });
            }

            if ( l ) {
                    fragment = jQuery.buildFragment( args, this[ 0 ].ownerDocument, false, !allowIntersection && this );
                    first = fragment.firstChild;

                    if ( fragment.childNodes.length === 1 ) {
                            fragment = first;
                    }

                    if ( first ) {
                            scripts = jQuery.map( getAll( fragment, "script" ), disableScript );
                            hasScripts = scripts.length;

                            // Use the original fragment for the last item instead of the first because it can end up
                            // being emptied incorrectly in certain situations (#8070).
                            for ( ; i < l; i++ ) {
                                    node = fragment;

                                    if ( i !== iNoClone ) {
                                            node = jQuery.clone( node, true, true );

                                            // Keep references to cloned scripts for later restoration
                                            if ( hasScripts ) {
                                                    // Support: QtWebKit
                                                    // jQuery.merge because core_push.apply(_, arraylike) throws
                                                    jQuery.merge( scripts, getAll( node, "script" ) );
                                            }
                                    }

                                    callback.call( this[ i ], node, i );
                            }

                            if ( hasScripts ) {
                                    doc = scripts[ scripts.length - 1 ].ownerDocument;

                                    // Reenable scripts
                                    jQuery.map( scripts, restoreScript );

                                    // Evaluate executable scripts on first document insertion
                                    for ( i = 0; i < hasScripts; i++ ) {
                                            node = scripts[ i ];
                                            if ( rscriptType.test( node.type || "" ) &&
                                                    !data_priv.access( node, "globalEval" ) && jQuery.contains( doc, node ) ) {

                                                    if ( node.src ) {
                                                            // Hope ajax is available...
                                                            jQuery._evalUrl( node.src );
                                                    } else {
                                                            jQuery.globalEval( node.textContent.replace( rcleanScript, "" ) );
                                                    }
                                            }
                                    }
                            }
                    }
            }

            return this;
    }
});

jQuery.each({
    appendTo: "append",
    prependTo: "prepend",
    insertBefore: "before",
    insertAfter: "after",
    replaceAll: "replaceWith"
}, function( name, original ) {
    jQuery.fn[ name ] = function( selector ) {
            var elems,
                    ret = [],
                    insert = jQuery( selector ),
                    last = insert.length - 1,
                    i = 0;

            for ( ; i <= last; i++ ) {
                    elems = i === last ? this : this.clone( true );
                    jQuery( insert[ i ] )[ original ]( elems );

                    // Support: QtWebKit
                    // .get() because core_push.apply(_, arraylike) throws
                    core_push.apply( ret, elems.get() );
            }

            return this.pushStack( ret );
    };
});

jQuery.extend({
    clone: function( elem, dataAndEvents, deepDataAndEvents ) {
            var i, l, srcElements, destElements,
                    clone = elem.cloneNode( true ),
                    inPage = jQuery.contains( elem.ownerDocument, elem );

            // Support: IE >= 9
            // Fix Cloning issues
            if ( !jQuery.support.noCloneChecked && ( elem.nodeType === 1 || elem.nodeType === 11 ) && !jQuery.isXMLDoc( elem ) ) {

                    // We eschew Sizzle here for performance reasons: http://jsperf.com/getall-vs-sizzle/2
                    destElements = getAll( clone );
                    srcElements = getAll( elem );

                    for ( i = 0, l = srcElements.length; i < l; i++ ) {
                            fixInput( srcElements[ i ], destElements[ i ] );
                    }
            }

            // Copy the events from the original to the clone
            if ( dataAndEvents ) {
                    if ( deepDataAndEvents ) {
                            srcElements = srcElements || getAll( elem );
                            destElements = destElements || getAll( clone );

                            for ( i = 0, l = srcElements.length; i < l; i++ ) {
                                    cloneCopyEvent( srcElements[ i ], destElements[ i ] );
                            }
                    } else {
                            cloneCopyEvent( elem, clone );
                    }
            }

            // Preserve script evaluation history
            destElements = getAll( clone, "script" );
            if ( destElements.length > 0 ) {
                    setGlobalEval( destElements, !inPage && getAll( elem, "script" ) );
            }

            // Return the cloned set
            return clone;
    },

    buildFragment: function( elems, context, scripts, selection ) {
            var elem, tmp, tag, wrap, contains, j,
                    i = 0,
                    l = elems.length,
                    fragment = context.createDocumentFragment(),
                    nodes = [];

            for ( ; i < l; i++ ) {
                    elem = elems[ i ];

                    if ( elem || elem === 0 ) {

                            // Add nodes directly
                            if ( jQuery.type( elem ) === "object" ) {
                                    // Support: QtWebKit
                                    // jQuery.merge because core_push.apply(_, arraylike) throws
                                    jQuery.merge( nodes, elem.nodeType ? [ elem ] : elem );

                            // Convert non-html into a text node
                            } else if ( !rhtml.test( elem ) ) {
                                    nodes.push( context.createTextNode( elem ) );

                            // Convert html into DOM nodes
                            } else {
                                    tmp = tmp || fragment.appendChild( context.createElement("div") );

                                    // Deserialize a standard representation
                                    tag = ( rtagName.exec( elem ) || ["", ""] )[ 1 ].toLowerCase();
                                    wrap = wrapMap[ tag ] || wrapMap._default;
                                    tmp.innerHTML = wrap[ 1 ] + elem.replace( rxhtmlTag, "<$1></$2>" ) + wrap[ 2 ];

                                    // Descend through wrappers to the right content
                                    j = wrap[ 0 ];
                                    while ( j-- ) {
                                            tmp = tmp.lastChild;
                                    }

                                    // Support: QtWebKit
                                    // jQuery.merge because core_push.apply(_, arraylike) throws
                                    jQuery.merge( nodes, tmp.childNodes );

                                    // Remember the top-level container
                                    tmp = fragment.firstChild;

                                    // Fixes #12346
                                    // Support: Webkit, IE
                                    tmp.textContent = "";
                            }
                    }
            }

            // Remove wrapper from fragment
            fragment.textContent = "";

            i = 0;
            while ( (elem = nodes[ i++ ]) ) {

                    // #4087 - If origin and destination elements are the same, and this is
                    // that element, do not do anything
                    if ( selection && jQuery.inArray( elem, selection ) !== -1 ) {
                            continue;
                    }

                    contains = jQuery.contains( elem.ownerDocument, elem );

                    // Append to fragment
                    tmp = getAll( fragment.appendChild( elem ), "script" );

                    // Preserve script evaluation history
                    if ( contains ) {
                            setGlobalEval( tmp );
                    }

                    // Capture executables
                    if ( scripts ) {
                            j = 0;
                            while ( (elem = tmp[ j++ ]) ) {
                                    if ( rscriptType.test( elem.type || "" ) ) {
                                            scripts.push( elem );
                                    }
                            }
                    }
            }

            return fragment;
    },

    cleanData: function( elems ) {
            var data, elem, events, type, key, j,
                    special = jQuery.event.special,
                    i = 0;

            for ( ; (elem = elems[ i ]) !== undefined; i++ ) {
                    if ( Data.accepts( elem ) ) {
                            key = elem[ data_priv.expando ];

                            if ( key && (data = data_priv.cache[ key ]) ) {
                                    events = Object.keys( data.events || {} );
                                    if ( events.length ) {
                                            for ( j = 0; (type = events[j]) !== undefined; j++ ) {
                                                    if ( special[ type ] ) {
                                                            jQuery.event.remove( elem, type );

                                                    // This is a shortcut to avoid jQuery.event.remove's overhead
                                                    } else {
                                                            jQuery.removeEvent( elem, type, data.handle );
                                                    }
                                            }
                                    }
                                    if ( data_priv.cache[ key ] ) {
                                            // Discard any remaining `private` data
                                            delete data_priv.cache[ key ];
                                    }
                            }
                    }
                    // Discard any remaining `user` data
                    delete data_user.cache[ elem[ data_user.expando ] ];
            }
    },

    _evalUrl: function( url ) {
            return jQuery.ajax({
                    url: url,
                    type: "GET",
                    dataType: "script",
                    async: false,
                    global: false,
                    "throws": true
            });
    }
});

// Support: 1.x compatibility
// Manipulating tables requires a tbody
function manipulationTarget( elem, content ) {
    return jQuery.nodeName( elem, "table" ) &&
            jQuery.nodeName( content.nodeType === 1 ? content : content.firstChild, "tr" ) ?

            elem.getElementsByTagName("tbody")[0] ||
                    elem.appendChild( elem.ownerDocument.createElement("tbody") ) :
            elem;
}

// Replace/restore the type attribute of script elements for safe DOM manipulation
function disableScript( elem ) {
    elem.type = (elem.getAttribute("type") !== null) + "/" + elem.type;
    return elem;
}
function restoreScript( elem ) {
    var match = rscriptTypeMasked.exec( elem.type );

    if ( match ) {
            elem.type = match[ 1 ];
    } else {
            elem.removeAttribute("type");
    }

    return elem;
}

function setGlobalEval( elems, refElements ) {
    var l = elems.length,
            i = 0;

    for ( ; i < l; i++ ) {
            data_priv.set(
                    elems[ i ], "globalEval", !refElements || data_priv.get( refElements[ i ], "globalEval" )
            );
    }
}

function cloneCopyEvent( src, dest ) {
    var i, l, type, pdataOld, pdataCur, udataOld, udataCur, events;

    if ( dest.nodeType !== 1 ) {
            return;
    }

    // 1. Copy private data: events, handlers, etc.
    if ( data_priv.hasData( src ) ) {
            pdataOld = data_priv.access( src );
            pdataCur = data_priv.set( dest, pdataOld );
            events = pdataOld.events;

            if ( events ) {
                    delete pdataCur.handle;
                    pdataCur.events = {};

                    for ( type in events ) {
                            for ( i = 0, l = events[ type ].length; i < l; i++ ) {
                                    jQuery.event.add( dest, type, events[ type ][ i ] );
                            }
                    }
            }
    }
    if ( data_user.hasData( src ) ) {
            udataOld = data_user.access( src );
            udataCur = jQuery.extend( {}, udataOld );

            data_user.set( dest, udataCur );
    }
}

function getAll( context, tag ) {
    var ret = context.getElementsByTagName ? context.getElementsByTagName( tag || "*" ) :
                    context.querySelectorAll ? context.querySelectorAll( tag || "*" ) :
                    [];

    return tag === undefined || tag && jQuery.nodeName( context, tag ) ?
            jQuery.merge( [ context ], ret ) :
            ret;
}

function fixInput( src, dest ) {
    var nodeName = dest.nodeName.toLowerCase();

    if ( nodeName === "input" && manipulation_rcheckableType.test( src.type ) ) {
            dest.checked = src.checked;

    } else if ( nodeName === "input" || nodeName === "textarea" ) {
            dest.defaultValue = src.defaultValue;
    }
}
jQuery.fn.extend({
    wrapAll: function( html ) {
            var wrap;

            if ( jQuery.isFunction( html ) ) {
                    return this.each(function( i ) {
                            jQuery( this ).wrapAll( html.call(this, i) );
                    });
            }

            if ( this[ 0 ] ) {

                    wrap = jQuery( html, this[ 0 ].ownerDocument ).eq( 0 ).clone( true );
                    if ( this[ 0 ].parentNode ) {
                            wrap.insertBefore( this[ 0 ] );
                    }

                    wrap.map(function() {
                            var elem = this;

                            while ( elem.firstElementChild ) {
                                    elem = elem.firstElementChild;
                            }

                            return elem;
                    }).append( this );
            }
            return this;
    },

    wrapInner: function( html ) {
            if ( jQuery.isFunction( html ) ) {
                    return this.each(function( i ) {
                            jQuery( this ).wrapInner( html.call(this, i) );
                    });
            }

            return this.each(function() {
                    var self = jQuery( this ),
                            contents = self.contents();

                    if ( contents.length ) {
                            contents.wrapAll( html );

                    } else {
                            self.append( html );
                    }
            });
    },

    wrap: function( html ) {
            var isFunction = jQuery.isFunction( html );

            return this.each(function( i ) {
                    jQuery( this ).wrapAll( isFunction ? html.call(this, i) : html );
            });
    },

    unwrap: function() {
            return this.parent().each(function() {
                    if ( !jQuery.nodeName( this, "body" ) ) {
                            jQuery( this ).replaceWith( this.childNodes );
                    }
            }).end();
    }
});
var curCSS, iframe,
    rdisplayswap = /^(none|table(?!-c[ea]).+)/,
    rmargin = /^margin/,
    rnumsplit = new RegExp( "^(" + core_pnum + ")(.*)$", "i" ),
    rnumnonpx = new RegExp( "^(" + core_pnum + ")(?!px)[a-z%]+$", "i" ),
    rrelNum = new RegExp( "^([+-])=(" + core_pnum + ")", "i" ),
    elemdisplay = { BODY: "block" },
    cssShow = { position: "absolute", visibility: "hidden", display: "block" },
    cssNormalTransform = {
            letterSpacing: 0,
            fontWeight: 400
    },
    cssExpand = [ "Top", "Right", "Bottom", "Left" ],
    cssPrefixes = [ "Webkit", "O", "Moz", "ms" ];

function vendorPropName( style, name ) {

    if ( name in style ) {
            return name;
    }

    // check for vendor prefixed names
    var capName = name.charAt(0).toUpperCase() + name.slice(1),
            origName = name,
            i = cssPrefixes.length;

    while ( i-- ) {
            name = cssPrefixes[ i ] + capName;
            if ( name in style ) {
                    return name;
            }
    }

    return origName;
}

function isHidden( elem, el ) {
    elem = el || elem;
    return jQuery.css( elem, "display" ) === "none" || !jQuery.contains( elem.ownerDocument, elem );
}

function getStyles( elem ) {
    return window.getComputedStyle( elem, null );
}

function showHide( elements, show ) {
    var display, elem, hidden,
            values = [],
            index = 0,
            length = elements.length;

    for ( ; index < length; index++ ) {
            elem = elements[ index ];
            if ( !elem.style ) {
                    continue;
            }

            values[ index ] = data_priv.get( elem, "olddisplay" );
            display = elem.style.display;
            if ( show ) {
                    if ( !values[ index ] && display === "none" ) {
                            elem.style.display = "";
                    }
                    if ( elem.style.display === "" && isHidden( elem ) ) {
                            values[ index ] = data_priv.access( elem, "olddisplay", css_defaultDisplay(elem.nodeName) );
                    }
            } else {

                    if ( !values[ index ] ) {
                            hidden = isHidden( elem );

                            if ( display && display !== "none" || !hidden ) {
                                    data_priv.set( elem, "olddisplay", hidden ? display : jQuery.css(elem, "display") );
                            }
                    }
            }
    }
    for ( index = 0; index < length; index++ ) {
            elem = elements[ index ];
            if ( !elem.style ) {
                    continue;
            }
            if ( !show || elem.style.display === "none" || elem.style.display === "" ) {
                    elem.style.display = show ? values[ index ] || "" : "none";
            }
    }

    return elements;
}

jQuery.fn.extend({
    css: function( name, value ) {
            return jQuery.access( this, function( elem, name, value ) {
                    var styles, len,
                            map = {},
                            i = 0;

                    if ( jQuery.isArray( name ) ) {
                            styles = getStyles( elem );
                            len = name.length;

                            for ( ; i < len; i++ ) {
                                    map[ name[ i ] ] = jQuery.css( elem, name[ i ], false, styles );
                            }

                            return map;
                    }

                    return value !== undefined ?
                            jQuery.style( elem, name, value ) :
                            jQuery.css( elem, name );
            }, name, value, arguments.length > 1 );
    },
    show: function() {
            return showHide( this, true );
    },
    hide: function() {
            return showHide( this );
    },
    toggle: function( state ) {
            if ( typeof state === "boolean" ) {
                    return state ? this.show() : this.hide();
            }

            return this.each(function() {
                    if ( isHidden( this ) ) {
                            jQuery( this ).show();
                    } else {
                            jQuery( this ).hide();
                    }
            });
    }
});

jQuery.extend({
    cssHooks: {
            opacity: {
                    get: function( elem, computed ) {
                            if ( computed ) {
                                    // We should always get a number back from opacity
                                    var ret = curCSS( elem, "opacity" );
                                    return ret === "" ? "1" : ret;
                            }
                    }
            }
    },

    cssNumber: {
            "columnCount": true,
            "fillOpacity": true,
            "fontWeight": true,
            "lineHeight": true,
            "opacity": true,
            "order": true,
            "orphans": true,
            "widows": true,
            "zIndex": true,
            "zoom": true
    },

    // Add in properties whose names you wish to fix before
    // setting or getting the value
    cssProps: {
            // normalize float css property
            "float": "cssFloat"
    },

    // Get and set the style property on a DOM Node
    style: function( elem, name, value, extra ) {
            // Don't set styles on text and comment nodes
            if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
                    return;
            }

            // Make sure that we're working with the right name
            var ret, type, hooks,
                    origName = jQuery.camelCase( name ),
                    style = elem.style;

            name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( style, origName ) );

            // gets hook for the prefixed version
            // followed by the unprefixed version
            hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

            // Check if we're setting a value
            if ( value !== undefined ) {
                    type = typeof value;

                    // convert relative number strings (+= or -=) to relative numbers. #7345
                    if ( type === "string" && (ret = rrelNum.exec( value )) ) {
                            value = ( ret[1] + 1 ) * ret[2] + parseFloat( jQuery.css( elem, name ) );
                            // Fixes bug #9237
                            type = "number";
                    }

                    // Make sure that NaN and null values aren't set. See: #7116
                    if ( value == null || type === "number" && isNaN( value ) ) {
                            return;
                    }

                    // If a number was passed in, add 'px' to the (except for certain CSS properties)
                    if ( type === "number" && !jQuery.cssNumber[ origName ] ) {
                            value += "px";
                    }

                    // Fixes #8908, it can be done more correctly by specifying setters in cssHooks,
                    // but it would mean to define eight (for every problematic property) identical functions
                    if ( !jQuery.support.clearCloneStyle && value === "" && name.indexOf("background") === 0 ) {
                            style[ name ] = "inherit";
                    }

                    // If a hook was provided, use that value, otherwise just set the specified value
                    if ( !hooks || !("set" in hooks) || (value = hooks.set( elem, value, extra )) !== undefined ) {
                            style[ name ] = value;
                    }

            } else {
                    // If a hook was provided get the non-computed value from there
                    if ( hooks && "get" in hooks && (ret = hooks.get( elem, false, extra )) !== undefined ) {
                            return ret;
                    }

                    // Otherwise just get the value from the style object
                    return style[ name ];
            }
    },

    css: function( elem, name, extra, styles ) {
            var val, num, hooks,
                    origName = jQuery.camelCase( name );

            // Make sure that we're working with the right name
            name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( elem.style, origName ) );

            // gets hook for the prefixed version
            // followed by the unprefixed version
            hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

            // If a hook was provided get the computed value from there
            if ( hooks && "get" in hooks ) {
                    val = hooks.get( elem, true, extra );
            }

            // Otherwise, if a way to get the computed value exists, use that
            if ( val === undefined ) {
                    val = curCSS( elem, name, styles );
            }

            //convert "normal" to computed value
            if ( val === "normal" && name in cssNormalTransform ) {
                    val = cssNormalTransform[ name ];
            }

            // Return, converting to number if forced or a qualifier was provided and val looks numeric
            if ( extra === "" || extra ) {
                    num = parseFloat( val );
                    return extra === true || jQuery.isNumeric( num ) ? num || 0 : val;
            }
            return val;
    }
});

curCSS = function( elem, name, _computed ) {
    var width, minWidth, maxWidth,
            computed = _computed || getStyles( elem ),
            ret = computed ? computed.getPropertyValue( name ) || computed[ name ] : undefined,
            style = elem.style;

    if ( computed ) {

            if ( ret === "" && !jQuery.contains( elem.ownerDocument, elem ) ) {
                    ret = jQuery.style( elem, name );
            }
            if ( rnumnonpx.test( ret ) && rmargin.test( name ) ) {

                    width = style.width;
                    minWidth = style.minWidth;
                    maxWidth = style.maxWidth;
                    style.minWidth = style.maxWidth = style.width = ret;
                    ret = computed.width;
                    style.width = width;
                    style.minWidth = minWidth;
                    style.maxWidth = maxWidth;
            }
    }

    return ret;
};


function setPositiveNumber( elem, value, subtract ) {
    var matches = rnumsplit.exec( value );
    return matches ?
            // Guard against undefined "subtract", e.g., when used as in cssHooks
            Math.max( 0, matches[ 1 ] - ( subtract || 0 ) ) + ( matches[ 2 ] || "px" ) :
            value;
}

function augmentWidthOrHeight( elem, name, extra, isBorderBox, styles ) {
    var i = extra === ( isBorderBox ? "border" : "content" ) ?
            // If we already have the right measurement, avoid augmentation
            4 :
            // Otherwise initialize for horizontal or vertical properties
            name === "width" ? 1 : 0,

            val = 0;

    for ( ; i < 4; i += 2 ) {
            // both box models exclude margin, so add it if we want it
            if ( extra === "margin" ) {
                    val += jQuery.css( elem, extra + cssExpand[ i ], true, styles );
            }

            if ( isBorderBox ) {
                    // border-box includes padding, so remove it if we want content
                    if ( extra === "content" ) {
                            val -= jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );
                    }

                    // at this point, extra isn't border nor margin, so remove border
                    if ( extra !== "margin" ) {
                            val -= jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
                    }
            } else {
                    // at this point, extra isn't content, so add padding
                    val += jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );

                    // at this point, extra isn't content nor padding, so add border
                    if ( extra !== "padding" ) {
                            val += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
                    }
            }
    }

    return val;
}

function getWidthOrHeight( elem, name, extra ) {

    // Start with offset property, which is equivalent to the border-box value
    var valueIsBorderBox = true,
            val = name === "width" ? elem.offsetWidth : elem.offsetHeight,
            styles = getStyles( elem ),
            isBorderBox = jQuery.support.boxSizing && jQuery.css( elem, "boxSizing", false, styles ) === "border-box";

    // some non-html elements return undefined for offsetWidth, so check for null/undefined
    // svg - https://bugzilla.mozilla.org/show_bug.cgi?id=649285
    // MathML - https://bugzilla.mozilla.org/show_bug.cgi?id=491668
    if ( val <= 0 || val == null ) {
            // Fall back to computed then uncomputed css if necessary
            val = curCSS( elem, name, styles );
            if ( val < 0 || val == null ) {
                    val = elem.style[ name ];
            }

            // Computed unit is not pixels. Stop here and return.
            if ( rnumnonpx.test(val) ) {
                    return val;
            }

            // we need the check for style in case a browser which returns unreliable values
            // for getComputedStyle silently falls back to the reliable elem.style
            valueIsBorderBox = isBorderBox && ( jQuery.support.boxSizingReliable || val === elem.style[ name ] );

            // Normalize "", auto, and prepare for extra
            val = parseFloat( val ) || 0;
    }

    // use the active box-sizing model to add/subtract irrelevant styles
    return ( val +
            augmentWidthOrHeight(
                    elem,
                    name,
                    extra || ( isBorderBox ? "border" : "content" ),
                    valueIsBorderBox,
                    styles
            )
    ) + "px";
}

// Try to determine the default display value of an element
function css_defaultDisplay( nodeName ) {
    var doc = document,
            display = elemdisplay[ nodeName ];

    if ( !display ) {
            display = actualDisplay( nodeName, doc );

            // If the simple way fails, read from inside an iframe
            if ( display === "none" || !display ) {
                    // Use the already-created iframe if possible
                    iframe = ( iframe ||
                            jQuery("<iframe frameborder='0' width='0' height='0'/>")
                            .css( "cssText", "display:block !important" )
                    ).appendTo( doc.documentElement );

                    // Always write a new HTML skeleton so Webkit and Firefox don't choke on reuse
                    doc = ( iframe[0].contentWindow || iframe[0].contentDocument ).document;
                    doc.write("<!doctype html><html><body>");
                    doc.close();

                    display = actualDisplay( nodeName, doc );
                    iframe.detach();
            }

            // Store the correct default display
            elemdisplay[ nodeName ] = display;
    }

    return display;
}

// Called ONLY from within css_defaultDisplay
function actualDisplay( name, doc ) {
    var elem = jQuery( doc.createElement( name ) ).appendTo( doc.body ),
            display = jQuery.css( elem[0], "display" );
    elem.remove();
    return display;
}

jQuery.each([ "height", "width" ], function( i, name ) {
    jQuery.cssHooks[ name ] = {
            get: function( elem, computed, extra ) {
                    if ( computed ) {
                            // certain elements can have dimension info if we invisibly show them
                            // however, it must have a current display style that would benefit from this
                            return elem.offsetWidth === 0 && rdisplayswap.test( jQuery.css( elem, "display" ) ) ?
                                    jQuery.swap( elem, cssShow, function() {
                                            return getWidthOrHeight( elem, name, extra );
                                    }) :
                                    getWidthOrHeight( elem, name, extra );
                    }
            },

            set: function( elem, value, extra ) {
                    var styles = extra && getStyles( elem );
                    return setPositiveNumber( elem, value, extra ?
                            augmentWidthOrHeight(
                                    elem,
                                    name,
                                    extra,
                                    jQuery.support.boxSizing && jQuery.css( elem, "boxSizing", false, styles ) === "border-box",
                                    styles
                            ) : 0
                    );
            }
    };
});

// These hooks cannot be added until DOM ready because the support test
// for it is not run until after DOM ready
jQuery(function() {
    // Support: Android 2.3
    if ( !jQuery.support.reliableMarginRight ) {
            jQuery.cssHooks.marginRight = {
                    get: function( elem, computed ) {
                            if ( computed ) {
                                    // Support: Android 2.3
                                    // WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
                                    // Work around by temporarily setting element display to inline-block
                                    return jQuery.swap( elem, { "display": "inline-block" },
                                            curCSS, [ elem, "marginRight" ] );
                            }
                    }
            };
    }

    // Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
    // getComputedStyle returns percent when specified for top/left/bottom/right
    // rather than make the css module depend on the offset module, we just check for it here
    if ( !jQuery.support.pixelPosition && jQuery.fn.position ) {
            jQuery.each( [ "top", "left" ], function( i, prop ) {
                    jQuery.cssHooks[ prop ] = {
                            get: function( elem, computed ) {
                                    if ( computed ) {
                                            computed = curCSS( elem, prop );
                                            // if curCSS returns percentage, fallback to offset
                                            return rnumnonpx.test( computed ) ?
                                                    jQuery( elem ).position()[ prop ] + "px" :
                                                    computed;
                                    }
                            }
                    };
            });
    }

});

if ( jQuery.expr && jQuery.expr.filters ) {
    jQuery.expr.filters.hidden = function( elem ) {
            // Support: Opera <= 12.12
            // Opera reports offsetWidths and offsetHeights less than zero on some elements
            return elem.offsetWidth <= 0 && elem.offsetHeight <= 0;
    };

    jQuery.expr.filters.visible = function( elem ) {
            return !jQuery.expr.filters.hidden( elem );
    };
}

jQuery.each({
    margin: "",
    padding: "",
    border: "Width"
}, function( prefix, suffix ) {
    jQuery.cssHooks[ prefix + suffix ] = {
            expand: function( value ) {
                    var i = 0,
                            expanded = {},

                            // assumes a single number if not a string
                            parts = typeof value === "string" ? value.split(" ") : [ value ];

                    for ( ; i < 4; i++ ) {
                            expanded[ prefix + cssExpand[ i ] + suffix ] =
                                    parts[ i ] || parts[ i - 2 ] || parts[ 0 ];
                    }

                    return expanded;
            }
    };

    if ( !rmargin.test( prefix ) ) {
            jQuery.cssHooks[ prefix + suffix ].set = setPositiveNumber;
    }
});
var r20 = /%20/g,
    rbracket = /\[\]$/,
    rCRLF = /\r?\n/g,
    rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
    rsubmittable = /^(?:input|select|textarea|keygen)/i;

jQuery.fn.extend({
    serialize: function() {
            return jQuery.param( this.serializeArray() );
    },
    serializeArray: function() {
            return this.map(function(){
                    // Can add propHook for "elements" to filter or add form elements
                    var elements = jQuery.prop( this, "elements" );
                    return elements ? jQuery.makeArray( elements ) : this;
            })
            .filter(function(){
                    var type = this.type;
                    // Use .is(":disabled") so that fieldset[disabled] works
                    return this.name && !jQuery( this ).is( ":disabled" ) &&
                            rsubmittable.test( this.nodeName ) && !rsubmitterTypes.test( type ) &&
                            ( this.checked || !manipulation_rcheckableType.test( type ) );
            })
            .map(function( i, elem ){
                    var val = jQuery( this ).val();

                    return val == null ?
                            null :
                            jQuery.isArray( val ) ?
                                    jQuery.map( val, function( val ){
                                            return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
                                    }) :
                                    { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
            }).get();
    }
});

//Serialize an array of form elements or a set of
//key/values into a query string
jQuery.param = function( a, traditional ) {
    var prefix,
            s = [],
            add = function( key, value ) {
                    // If value is a function, invoke it and return its value
                    value = jQuery.isFunction( value ) ? value() : ( value == null ? "" : value );
                    s[ s.length ] = encodeURIComponent( key ) + "=" + encodeURIComponent( value );
            };

    // Set traditional to true for jQuery <= 1.3.2 behavior.
    if ( traditional === undefined ) {
            traditional = jQuery.ajaxSettings && jQuery.ajaxSettings.traditional;
    }

    // If an array was passed in, assume that it is an array of form elements.
    if ( jQuery.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {
            // Serialize the form elements
            jQuery.each( a, function() {
                    add( this.name, this.value );
            });

    } else {
            // If traditional, encode the "old" way (the way 1.3.2 or older
            // did it), otherwise encode params recursively.
            for ( prefix in a ) {
                    buildParams( prefix, a[ prefix ], traditional, add );
            }
    }

    // Return the resulting serialization
    return s.join( "&" ).replace( r20, "+" );
};

function buildParams( prefix, obj, traditional, add ) {
    var name;

    if ( jQuery.isArray( obj ) ) {
            // Serialize array item.
            jQuery.each( obj, function( i, v ) {
                    if ( traditional || rbracket.test( prefix ) ) {
                            // Treat each array item as a scalar.
                            add( prefix, v );

                    } else {
                            // Item is non-scalar (array or object), encode its numeric index.
                            buildParams( prefix + "[" + ( typeof v === "object" ? i : "" ) + "]", v, traditional, add );
                    }
            });

    } else if ( !traditional && jQuery.type( obj ) === "object" ) {
            // Serialize object item.
            for ( name in obj ) {
                    buildParams( prefix + "[" + name + "]", obj[ name ], traditional, add );
            }

    } else {
            // Serialize scalar item.
            add( prefix, obj );
    }
}
jQuery.each( ("blur focus focusin focusout load resize scroll unload click dblclick " +
    "mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
    "change select submit keydown keypress keyup error contextmenu").split(" "), function( i, name ) {

    jQuery.fn[ name ] = function( data, fn ) {
            return arguments.length > 0 ?
                    this.on( name, null, data, fn ) :
                    this.trigger( name );
    };
});
jQuery.fn.extend({
    hover: function( fnOver, fnOut ) {
            return this.mouseenter( fnOver ).mouseleave( fnOut || fnOver );
    },
    bind: function( types, data, fn ) {
            return this.on( types, null, data, fn );
    },
    unbind: function( types, fn ) {
            return this.off( types, null, fn );
    },
    delegate: function( selector, types, data, fn ) {
            return this.on( types, selector, data, fn );
    },
    undelegate: function( selector, types, fn ) {
            // ( namespace ) or ( selector, types [, fn] )
            return arguments.length === 1 ? this.off( selector, "**" ) : this.off( types, selector || "**", fn );
    }
});
var
    ajaxLocParts,
    ajaxLocation,
    ajax_nonce = jQuery.now(),
    ajax_rquery = /\?/,
    rhash = /#.*$/,
    rts = /([?&])_=[^&]*/,
    rheaders = /^(.*?):[ \t]*([^\r\n]*)$/mg,
    rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
    rnoContent = /^(?:GET|HEAD)$/,
    rprotocol = /^\/\//,
    rurl = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,
    _load = jQuery.fn.load,
    prefilters = {},
    transports = {},
    allTypes = "*/".concat("*");
try {
    ajaxLocation = location.href;
} catch( e ) {
    ajaxLocation = document.createElement( "a" );
    ajaxLocation.href = "";
    ajaxLocation = ajaxLocation.href;
}
ajaxLocParts = rurl.exec( ajaxLocation.toLowerCase() ) || [];

function addToPrefiltersOrTransports( structure ) {
    return function( dataTypeExpression, func ) {

            if ( typeof dataTypeExpression !== "string" ) {
                    func = dataTypeExpression;
                    dataTypeExpression = "*";
            }

            var dataType,
                    i = 0,
                    dataTypes = dataTypeExpression.toLowerCase().match( core_rnotwhite ) || [];

            if ( jQuery.isFunction( func ) ) {
                    while ( (dataType = dataTypes[i++]) ) {
                            if ( dataType[0] === "+" ) {
                                    dataType = dataType.slice( 1 ) || "*";
                                    (structure[ dataType ] = structure[ dataType ] || []).unshift( func );

                            } else {
                                    (structure[ dataType ] = structure[ dataType ] || []).push( func );
                            }
                    }
            }
    };
}
function inspectPrefiltersOrTransports( structure, options, originalOptions, jqXHR ) {
    var inspected = {},
            seekingTransport = ( structure === transports );

    function inspect( dataType ) {
            var selected;
            inspected[ dataType ] = true;
            jQuery.each( structure[ dataType ] || [], function( _, prefilterOrFactory ) {
                    var dataTypeOrTransport = prefilterOrFactory( options, originalOptions, jqXHR );
                    if( typeof dataTypeOrTransport === "string" && !seekingTransport && !inspected[ dataTypeOrTransport ] ) {
                            options.dataTypes.unshift( dataTypeOrTransport );
                            inspect( dataTypeOrTransport );
                            return false;
                    } else if ( seekingTransport ) {
                            return !( selected = dataTypeOrTransport );
                    }
            });
            return selected;
    }

    return inspect( options.dataTypes[ 0 ] ) || !inspected[ "*" ] && inspect( "*" );
}
function ajaxExtend( target, src ) {
    var key, deep,
            flatOptions = jQuery.ajaxSettings.flatOptions || {};

    for ( key in src ) {
            if ( src[ key ] !== undefined ) {
                    ( flatOptions[ key ] ? target : ( deep || (deep = {}) ) )[ key ] = src[ key ];
            }
    }
    if ( deep ) {
            jQuery.extend( true, target, deep );
    }

    return target;
}
jQuery.fn.load = function( url, params, callback ) {
    if ( typeof url !== "string" && _load ) {
            return _load.apply( this, arguments );
    }

    var selector, type, response,
            self = this,
            off = url.indexOf(" ");

    if ( off >= 0 ) {
            selector = url.slice( off );
            url = url.slice( 0, off );
    }

    // If it's a function
    if ( jQuery.isFunction( params ) ) {

            // We assume that it's the callback
            callback = params;
            params = undefined;

    // Otherwise, build a param string
    } else if ( params && typeof params === "object" ) {
            type = "POST";
    }

    // If we have elements to modify, make the request
    if ( self.length > 0 ) {
            jQuery.ajax({
                    url: url,

                    // if "type" variable is undefined, then "GET" method will be used
                    type: type,
                    dataType: "html",
                    data: params
            }).done(function( responseText ) {

                    // Save response for use in complete callback
                    response = arguments;

                    self.html( selector ?

                            // If a selector was specified, locate the right elements in a dummy div
                            // Exclude scripts to avoid IE 'Permission Denied' errors
                            jQuery("<div>").append( jQuery.parseHTML( responseText ) ).find( selector ) :

                            // Otherwise use the full result
                            responseText );

            }).complete( callback && function( jqXHR, status ) {
                    self.each( callback, response || [ jqXHR.responseText, status, jqXHR ] );
            });
    }

    return this;
};
jQuery.each( [ "ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend" ], function( i, type ){
    jQuery.fn[ type ] = function( fn ){
            return this.on( type, fn );
    };
});
jQuery.extend({
    active: 0,
    lastModified: {},
    etag: {},
    ajaxSettings: {
            url: ajaxLocation,
            type: "GET",
            isLocal: rlocalProtocol.test( ajaxLocParts[ 1 ] ),
            global: true,
            processData: true,
            async: true,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            accepts: {
                    "*": allTypes,
                    text: "text/plain",
                    html: "text/html",
                    xml: "application/xml, text/xml",
                    json: "application/json, text/javascript"
            },
            contents: {
                    xml: /xml/,
                    html: /html/,
                    json: /json/
            },
            responseFields: {
                    xml: "responseXML",
                    text: "responseText",
                    json: "responseJSON"
            },
            converters: {
                    "* text": String,
                    "text html": true,
                    "text json": jQuery.parseJSON,
                    "text xml": jQuery.parseXML
            },
            flatOptions: {
                    url: true,
                    context: true
            }
    },

    ajaxSetup: function( target, settings ) {
            return settings ?

                    // Building a settings object
                    ajaxExtend( ajaxExtend( target, jQuery.ajaxSettings ), settings ) :

                    // Extending ajaxSettings
                    ajaxExtend( jQuery.ajaxSettings, target );
    },

    ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
    ajaxTransport: addToPrefiltersOrTransports( transports ),

    ajax: function( url, options ) {
            if ( typeof url === "object" ) {
                    options = url;
                    url = undefined;
            }
            options = options || {};
            var transport,
                    cacheURL,
                    responseHeadersString,
                    responseHeaders,
                    timeoutTimer,
                    parts,
                    fireGlobals,
                    i,
                    s = jQuery.ajaxSetup( {}, options ),
                    callbackContext = s.context || s,
                    globalEventContext = s.context && ( callbackContext.nodeType || callbackContext.jquery ) ?
                            jQuery( callbackContext ) :
                            jQuery.event,
                    deferred = jQuery.Deferred(),
                    completeDeferred = jQuery.Callbacks("once memory"),
                    statusCode = s.statusCode || {},
                    requestHeaders = {},
                    requestHeadersNames = {},
                    state = 0,
                    strAbort = "canceled",
                    jqXHR = {
                            readyState: 0,
                            getResponseHeader: function( key ) {
                                    var match;
                                    if ( state === 2 ) {
                                            if ( !responseHeaders ) {
                                                    responseHeaders = {};
                                                    while ( (match = rheaders.exec( responseHeadersString )) ) {
                                                            responseHeaders[ match[1].toLowerCase() ] = match[ 2 ];
                                                    }
                                            }
                                            match = responseHeaders[ key.toLowerCase() ];
                                    }
                                    return match == null ? null : match;
                            },
                            getAllResponseHeaders: function() {
                                    return state === 2 ? responseHeadersString : null;
                            },
                            setRequestHeader: function( name, value ) {
                                    var lname = name.toLowerCase();
                                    if ( !state ) {
                                            name = requestHeadersNames[ lname ] = requestHeadersNames[ lname ] || name;
                                            requestHeaders[ name ] = value;
                                    }
                                    return this;
                            },
                            overrideMimeType: function( type ) {
                                    if ( !state ) {
                                            s.mimeType = type;
                                    }
                                    return this;
                            },
                            statusCode: function( map ) {
                                    var code;
                                    if ( map ) {
                                            if ( state < 2 ) {
                                                    for ( code in map ) {
                                                            // Lazy-add the new callback in a way that preserves old ones
                                                            statusCode[ code ] = [ statusCode[ code ], map[ code ] ];
                                                    }
                                            } else {
                                                    // Execute the appropriate callbacks
                                                    jqXHR.always( map[ jqXHR.status ] );
                                            }
                                    }
                                    return this;
                            },
                            abort: function( statusText ) {
                                    var finalText = statusText || strAbort;
                                    if ( transport ) {
                                            transport.abort( finalText );
                                    }
                                    done( 0, finalText );
                                    return this;
                            }
                    };

            deferred.promise( jqXHR ).complete = completeDeferred.add;
            jqXHR.success = jqXHR.done;
            jqXHR.error = jqXHR.fail;

            s.url = ( ( url || s.url || ajaxLocation ) + "" ).replace( rhash, "" )
                    .replace( rprotocol, ajaxLocParts[ 1 ] + "//" );

            s.type = options.method || options.type || s.method || s.type;

            s.dataTypes = jQuery.trim( s.dataType || "*" ).toLowerCase().match( core_rnotwhite ) || [""];

            if ( s.crossDomain == null ) {
                    parts = rurl.exec( s.url.toLowerCase() );
                    s.crossDomain = !!( parts &&
                            ( parts[ 1 ] !== ajaxLocParts[ 1 ] || parts[ 2 ] !== ajaxLocParts[ 2 ] ||
                                    ( parts[ 3 ] || ( parts[ 1 ] === "http:" ? "80" : "443" ) ) !==
                                            ( ajaxLocParts[ 3 ] || ( ajaxLocParts[ 1 ] === "http:" ? "80" : "443" ) ) )
                    );
            }

            if ( s.data && s.processData && typeof s.data !== "string" ) {
                    s.data = jQuery.param( s.data, s.traditional );
            }

            inspectPrefiltersOrTransports( prefilters, s, options, jqXHR );

            if ( state === 2 ) {
                    return jqXHR;
            }

            fireGlobals = s.global;

            if ( fireGlobals && jQuery.active++ === 0 ) {
                    jQuery.event.trigger("ajaxStart");
            }

            s.type = s.type.toUpperCase();

            s.hasContent = !rnoContent.test( s.type );

            cacheURL = s.url;

            if ( !s.hasContent ) {

                    if ( s.data ) {
                            cacheURL = ( s.url += ( ajax_rquery.test( cacheURL ) ? "&" : "?" ) + s.data );
                            delete s.data;
                    }

                    if ( s.cache === false ) {
                            s.url = rts.test( cacheURL ) ?

                                    cacheURL.replace( rts, "$1_=" + ajax_nonce++ ) :

                                    cacheURL + ( ajax_rquery.test( cacheURL ) ? "&" : "?" ) + "_=" + ajax_nonce++;
                    }
            }

            if ( s.ifModified ) {
                    if ( jQuery.lastModified[ cacheURL ] ) {
                            jqXHR.setRequestHeader( "If-Modified-Since", jQuery.lastModified[ cacheURL ] );
                    }
                    if ( jQuery.etag[ cacheURL ] ) {
                            jqXHR.setRequestHeader( "If-None-Match", jQuery.etag[ cacheURL ] );
                    }
            }

            if ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
                    jqXHR.setRequestHeader( "Content-Type", s.contentType );
            }

            jqXHR.setRequestHeader(
                    "Accept",
                    s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[0] ] ?
                            s.accepts[ s.dataTypes[0] ] + ( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
                            s.accepts[ "*" ]
            );

            for ( i in s.headers ) {
                    jqXHR.setRequestHeader( i, s.headers[ i ] );
            }

            if ( s.beforeSend && ( s.beforeSend.call( callbackContext, jqXHR, s ) === false || state === 2 ) ) {
                    return jqXHR.abort();
            }

            strAbort = "abort";

            for ( i in { success: 1, error: 1, complete: 1 } ) {
                    jqXHR[ i ]( s[ i ] );
            }
            transport = inspectPrefiltersOrTransports( transports, s, options, jqXHR );
            if ( !transport ) {
                    done( -1, "No Transport" );
            } else {
                    jqXHR.readyState = 1;

                    if ( fireGlobals ) {
                            globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
                    }
                    if ( s.async && s.timeout > 0 ) {
                            timeoutTimer = setTimeout(function() {
                                    jqXHR.abort("timeout");
                            }, s.timeout );
                    }
                    try {
                            state = 1;
                            transport.send( requestHeaders, done );
                    } catch ( e ) {
                            if ( state < 2 ) {
                                    done( -1, e );
                            } else {
                                    throw e;
                            }
                    }
            }
            function done( status, nativeStatusText, responses, headers ) {
                    var isSuccess, success, error, response, modified,
                            statusText = nativeStatusText;

                    if ( state === 2 ) {
                            return;
                    }

                    state = 2;

                    if ( timeoutTimer ) {
                            clearTimeout( timeoutTimer );
                    }

                    transport = undefined;

                    responseHeadersString = headers || "";

                    jqXHR.readyState = status > 0 ? 4 : 0;

                    isSuccess = status >= 200 && status < 300 || status === 304;

                    if ( responses ) {
                            response = ajaxHandleResponses( s, jqXHR, responses );
                    }

                    response = ajaxConvert( s, response, jqXHR, isSuccess );

                    if ( isSuccess ) {

                            // Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
                            if ( s.ifModified ) {
                                    modified = jqXHR.getResponseHeader("Last-Modified");
                                    if ( modified ) {
                                            jQuery.lastModified[ cacheURL ] = modified;
                                    }
                                    modified = jqXHR.getResponseHeader("etag");
                                    if ( modified ) {
                                            jQuery.etag[ cacheURL ] = modified;
                                    }
                            }

                            if ( status === 204 || s.type === "HEAD" ) {
                                    statusText = "nocontent";

                            } else if ( status === 304 ) {
                                    statusText = "notmodified";

                            } else {
                                    statusText = response.state;
                                    success = response.data;
                                    error = response.error;
                                    isSuccess = !error;
                            }
                    } else {
                            error = statusText;
                            if ( status || !statusText ) {
                                    statusText = "error";
                                    if ( status < 0 ) {
                                            status = 0;
                                    }
                            }
                    }

                    jqXHR.status = status;
                    jqXHR.statusText = ( nativeStatusText || statusText ) + "";

                    if ( isSuccess ) {
                            deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
                    } else {
                            deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
                    }

                    jqXHR.statusCode( statusCode );
                    statusCode = undefined;

                    if ( fireGlobals ) {
                            globalEventContext.trigger( isSuccess ? "ajaxSuccess" : "ajaxError",
                                    [ jqXHR, s, isSuccess ? success : error ] );
                    }

                    completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );

                    if ( fireGlobals ) {
                            globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );
                            if ( !( --jQuery.active ) ) {
                                    jQuery.event.trigger("ajaxStop");
                            }
                    }
            }

            return jqXHR;
    },

    getJSON: function( url, data, callback ) {
            return jQuery.get( url, data, callback, "json" );
    },

    getScript: function( url, callback ) {
            return jQuery.get( url, undefined, callback, "script" );
    }
});

jQuery.each( [ "get", "post" ], function( i, method ) {
    jQuery[ method ] = function( url, data, callback, type ) {
            if ( jQuery.isFunction( data ) ) {
                    type = type || callback;
                    callback = data;
                    data = undefined;
            }
            return jQuery.ajax({
                    url: url,
                    type: method,
                    dataType: type,
                    data: data,
                    success: callback
            });
    };
});

function ajaxHandleResponses( s, jqXHR, responses ) {

    var ct, type, finalDataType, firstDataType,
            contents = s.contents,
            dataTypes = s.dataTypes;

    while( dataTypes[ 0 ] === "*" ) {
            dataTypes.shift();
            if ( ct === undefined ) {
                    ct = s.mimeType || jqXHR.getResponseHeader("Content-Type");
            }
    }

    if ( ct ) {
            for ( type in contents ) {
                    if ( contents[ type ] && contents[ type ].test( ct ) ) {
                            dataTypes.unshift( type );
                            break;
                    }
            }
    }

    if ( dataTypes[ 0 ] in responses ) {
            finalDataType = dataTypes[ 0 ];
    } else {
            for ( type in responses ) {
                    if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[0] ] ) {
                            finalDataType = type;
                            break;
                    }
                    if ( !firstDataType ) {
                            firstDataType = type;
                    }
            }
            finalDataType = finalDataType || firstDataType;
    }

    if ( finalDataType ) {
            if ( finalDataType !== dataTypes[ 0 ] ) {
                    dataTypes.unshift( finalDataType );
            }
            return responses[ finalDataType ];
    }
}

function ajaxConvert( s, response, jqXHR, isSuccess ) {
    var conv2, current, conv, tmp, prev,
            converters = {},
            dataTypes = s.dataTypes.slice();

    if ( dataTypes[ 1 ] ) {
            for ( conv in s.converters ) {
                    converters[ conv.toLowerCase() ] = s.converters[ conv ];
            }
    }

    current = dataTypes.shift();

    while ( current ) {

            if ( s.responseFields[ current ] ) {
                    jqXHR[ s.responseFields[ current ] ] = response;
            }

            if ( !prev && isSuccess && s.dataFilter ) {
                    response = s.dataFilter( response, s.dataType );
            }

            prev = current;
            current = dataTypes.shift();

            if ( current ) {

                    if ( current === "*" ) {

                            current = prev;

                    } else if ( prev !== "*" && prev !== current ) {

                            conv = converters[ prev + " " + current ] || converters[ "* " + current ];

                            if ( !conv ) {
                                    for ( conv2 in converters ) {

                                            tmp = conv2.split( " " );
                                            if ( tmp[ 1 ] === current ) {

                                                    conv = converters[ prev + " " + tmp[ 0 ] ] ||
                                                            converters[ "* " + tmp[ 0 ] ];
                                                    if ( conv ) {
                                                            if ( conv === true ) {
                                                                    conv = converters[ conv2 ];

                                                            } else if ( converters[ conv2 ] !== true ) {
                                                                    current = tmp[ 0 ];
                                                                    dataTypes.unshift( tmp[ 1 ] );
                                                            }
                                                            break;
                                                    }
                                            }
                                    }
                            }

                            if ( conv !== true ) {

                                    if ( conv && s[ "throws" ] ) {
                                            response = conv( response );
                                    } else {
                                            try {
                                                    response = conv( response );
                                            } catch ( e ) {
                                                    return { state: "parsererror", error: conv ? e : "No conversion from " + prev + " to " + current };
                                            }
                                    }
                            }
                    }
            }
    }

    return { state: "success", data: response };
}
jQuery.ajaxSetup({
    accepts: {
            script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
    },
    contents: {
            script: /(?:java|ecma)script/
    },
    converters: {
            "text script": function( text ) {
                    jQuery.globalEval( text );
                    return text;
            }
    }
});
jQuery.ajaxPrefilter( "script", function( s ) {
    if ( s.cache === undefined ) {
            s.cache = false;
    }
    if ( s.crossDomain ) {
            s.type = "GET";
    }
});
jQuery.ajaxTransport( "script", function( s ) {
    if ( s.crossDomain ) {
            var script, callback;
            return {
                    send: function( _, complete ) {
                            script = jQuery("<script>").prop({
                                    async: true,
                                    charset: s.scriptCharset,
                                    src: s.url
                            }).on(
                                    "load error",
                                    callback = function( evt ) {
                                            script.remove();
                                            callback = null;
                                            if ( evt ) {
                                                    complete( evt.type === "error" ? 404 : 200, evt.type );
                                            }
                                    }
                            );
                            document.head.appendChild( script[ 0 ] );
                    },
                    abort: function() {
                            if ( callback ) {
                                    callback();
                            }
                    }
            };
    }
});
var oldCallbacks = [],
    rjsonp = /(=)\?(?=&|$)|\?\?/;

jQuery.ajaxSetup({
    jsonp: "callback",
    jsonpCallback: function() {
            var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( ajax_nonce++ ) );
            this[ callback ] = true;
            return callback;
    }
});
jQuery.ajaxPrefilter( "json jsonp", function( s, originalSettings, jqXHR ) {

    var callbackName, overwritten, responseContainer,
            jsonProp = s.jsonp !== false && ( rjsonp.test( s.url ) ?
                    "url" :
                    typeof s.data === "string" && !( s.contentType || "" ).indexOf("application/x-www-form-urlencoded") && rjsonp.test( s.data ) && "data"
            );

    if ( jsonProp || s.dataTypes[ 0 ] === "jsonp" ) {

            callbackName = s.jsonpCallback = jQuery.isFunction( s.jsonpCallback ) ?
                    s.jsonpCallback() :
                    s.jsonpCallback;

            if ( jsonProp ) {
                    s[ jsonProp ] = s[ jsonProp ].replace( rjsonp, "$1" + callbackName );
            } else if ( s.jsonp !== false ) {
                    s.url += ( ajax_rquery.test( s.url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
            }

            s.converters["script json"] = function() {
                    if ( !responseContainer ) {
                            jQuery.error( callbackName + " was not called" );
                    }
                    return responseContainer[ 0 ];
            };

            s.dataTypes[ 0 ] = "json";

            overwritten = window[ callbackName ];
            window[ callbackName ] = function() {
                    responseContainer = arguments;
            };

            jqXHR.always(function() {
                    window[ callbackName ] = overwritten;

                    if ( s[ callbackName ] ) {
                            s.jsonpCallback = originalSettings.jsonpCallback;
                            oldCallbacks.push( callbackName );
                    }

                    if ( responseContainer && jQuery.isFunction( overwritten ) ) {
                            overwritten( responseContainer[ 0 ] );
                    }

                    responseContainer = overwritten = undefined;
            });
            return "script";
    }
});
jQuery.ajaxSettings.xhr = function() {
    try {
            return new XMLHttpRequest();
    } catch( e ) {}
};

var xhrSupported = jQuery.ajaxSettings.xhr(),
    xhrSuccessStatus = {
            0: 200,
            1223: 204
    },
    xhrId = 0,
    xhrCallbacks = {};

if ( window.ActiveXObject ) {
    jQuery( window ).on( "unload", function() {
            for( var key in xhrCallbacks ) {
                    xhrCallbacks[ key ]();
            }
            xhrCallbacks = undefined;
    });
}
jQuery.support.cors = !!xhrSupported && ( "withCredentials" in xhrSupported );
jQuery.support.ajax = xhrSupported = !!xhrSupported;
jQuery.ajaxTransport(function( options ) {
    var callback;
    if ( jQuery.support.cors || xhrSupported && !options.crossDomain ) {
            return {
                    send: function( headers, complete ) {
                            var i, id,
                                    xhr = options.xhr();
                            xhr.open( options.type, options.url, options.async, options.username, options.password );
                            if ( options.xhrFields ) {
                                    for ( i in options.xhrFields ) {
                                            xhr[ i ] = options.xhrFields[ i ];
                                    }
                            }
                            if ( options.mimeType && xhr.overrideMimeType ) {
                                    xhr.overrideMimeType( options.mimeType );
                            }
                            if ( !options.crossDomain && !headers["X-Requested-With"] ) {
                                    headers["X-Requested-With"] = "XMLHttpRequest";
                            }
                            for ( i in headers ) {
                                    xhr.setRequestHeader( i, headers[ i ] );
                            }
                            // Callback
                            callback = function( type ) {
                                    return function() {
                                            if ( callback ) {
                                                    delete xhrCallbacks[ id ];
                                                    callback = xhr.onload = xhr.onerror = null;
                                                    if ( type === "abort" ) {
                                                            xhr.abort();
                                                    } else if ( type === "error" ) {
                                                            complete(
                                                                    xhr.status || 404,
                                                                    xhr.statusText
                                                            );
                                                    } else {
                                                            complete(
                                                                    xhrSuccessStatus[ xhr.status ] || xhr.status,
                                                                    xhr.statusText,
                                                                    typeof xhr.responseText === "string" ? {
                                                                            text: xhr.responseText
                                                                    } : undefined,
                                                                    xhr.getAllResponseHeaders()
                                                            );
                                                    }
                                            }
                                    };
                            };
                            xhr.onload = callback();
                            xhr.onerror = callback("error");
                            callback = xhrCallbacks[( id = xhrId++ )] = callback("abort");
                            xhr.send( options.hasContent && options.data || null );
                    },
                    abort: function() {
                            if ( callback ) {
                                    callback();
                            }
                    }
            };
    }
});
var fxNow, timerId,
    rfxtypes = /^(?:toggle|show|hide)$/,
    rfxnum = new RegExp( "^(?:([+-])=|)(" + core_pnum + ")([a-z%]*)$", "i" ),
    rrun = /queueHooks$/,
    animationPrefilters = [ defaultPrefilter ],
    tweeners = {
            "*": [function( prop, value ) {
                    var tween = this.createTween( prop, value ),
                            target = tween.cur(),
                            parts = rfxnum.exec( value ),
                            unit = parts && parts[ 3 ] || ( jQuery.cssNumber[ prop ] ? "" : "px" ),

                            start = ( jQuery.cssNumber[ prop ] || unit !== "px" && +target ) &&
                                    rfxnum.exec( jQuery.css( tween.elem, prop ) ),
                            scale = 1,
                            maxIterations = 20;

                    if ( start && start[ 3 ] !== unit ) {
                            unit = unit || start[ 3 ];
                            parts = parts || [];
                            start = +target || 1;

                            do {
                                    scale = scale || ".5";

                                    start = start / scale;
                                    jQuery.style( tween.elem, prop, start + unit );

                            } while ( scale !== (scale = tween.cur() / target) && scale !== 1 && --maxIterations );
                    }
                    if ( parts ) {
                            start = tween.start = +start || +target || 0;
                            tween.unit = unit;
                            tween.end = parts[ 1 ] ?
                                    start + ( parts[ 1 ] + 1 ) * parts[ 2 ] :
                                    +parts[ 2 ];
                    }

                    return tween;
            }]
    };

function createFxNow() {
    setTimeout(function() {
            fxNow = undefined;
    });
    return ( fxNow = jQuery.now() );
}

function createTween( value, prop, animation ) {
    var tween,
            collection = ( tweeners[ prop ] || [] ).concat( tweeners[ "*" ] ),
            index = 0,
            length = collection.length;
    for ( ; index < length; index++ ) {
            if ( (tween = collection[ index ].call( animation, prop, value )) ) {

                    return tween;
            }
    }
}

function Animation( elem, properties, options ) {
    var result,
            stopped,
            index = 0,
            length = animationPrefilters.length,
            deferred = jQuery.Deferred().always( function() {
                    // don't match elem in the :animated selector
                    delete tick.elem;
            }),
            tick = function() {
                    if ( stopped ) {
                            return false;
                    }
                    var currentTime = fxNow || createFxNow(),
                            remaining = Math.max( 0, animation.startTime + animation.duration - currentTime ),
                            // archaic crash bug won't allow us to use 1 - ( 0.5 || 0 ) (#12497)
                            temp = remaining / animation.duration || 0,
                            percent = 1 - temp,
                            index = 0,
                            length = animation.tweens.length;

                    for ( ; index < length ; index++ ) {
                            animation.tweens[ index ].run( percent );
                    }

                    deferred.notifyWith( elem, [ animation, percent, remaining ]);

                    if ( percent < 1 && length ) {
                            return remaining;
                    } else {
                            deferred.resolveWith( elem, [ animation ] );
                            return false;
                    }
            },
            animation = deferred.promise({
                    elem: elem,
                    props: jQuery.extend( {}, properties ),
                    opts: jQuery.extend( true, { specialEasing: {} }, options ),
                    originalProperties: properties,
                    originalOptions: options,
                    startTime: fxNow || createFxNow(),
                    duration: options.duration,
                    tweens: [],
                    createTween: function( prop, end ) {
                            var tween = jQuery.Tween( elem, animation.opts, prop, end,
                                            animation.opts.specialEasing[ prop ] || animation.opts.easing );
                            animation.tweens.push( tween );
                            return tween;
                    },
                    stop: function( gotoEnd ) {
                            var index = 0,
                                    // if we are going to the end, we want to run all the tweens
                                    // otherwise we skip this part
                                    length = gotoEnd ? animation.tweens.length : 0;
                            if ( stopped ) {
                                    return this;
                            }
                            stopped = true;
                            for ( ; index < length ; index++ ) {
                                    animation.tweens[ index ].run( 1 );
                            }

                            // resolve when we played the last frame
                            // otherwise, reject
                            if ( gotoEnd ) {
                                    deferred.resolveWith( elem, [ animation, gotoEnd ] );
                            } else {
                                    deferred.rejectWith( elem, [ animation, gotoEnd ] );
                            }
                            return this;
                    }
            }),
            props = animation.props;

    propFilter( props, animation.opts.specialEasing );

    for ( ; index < length ; index++ ) {
            result = animationPrefilters[ index ].call( animation, elem, props, animation.opts );
            if ( result ) {
                    return result;
            }
    }

    jQuery.map( props, createTween, animation );

    if ( jQuery.isFunction( animation.opts.start ) ) {
            animation.opts.start.call( elem, animation );
    }

    jQuery.fx.timer(
            jQuery.extend( tick, {
                    elem: elem,
                    anim: animation,
                    queue: animation.opts.queue
            })
    );

    // attach callbacks from options
    return animation.progress( animation.opts.progress )
            .done( animation.opts.done, animation.opts.complete )
            .fail( animation.opts.fail )
            .always( animation.opts.always );
}

function propFilter( props, specialEasing ) {
    var index, name, easing, value, hooks;

    // camelCase, specialEasing and expand cssHook pass
    for ( index in props ) {
            name = jQuery.camelCase( index );
            easing = specialEasing[ name ];
            value = props[ index ];
            if ( jQuery.isArray( value ) ) {
                    easing = value[ 1 ];
                    value = props[ index ] = value[ 0 ];
            }

            if ( index !== name ) {
                    props[ name ] = value;
                    delete props[ index ];
            }

            hooks = jQuery.cssHooks[ name ];
            if ( hooks && "expand" in hooks ) {
                    value = hooks.expand( value );
                    delete props[ name ];

                    // not quite $.extend, this wont overwrite keys already present.
                    // also - reusing 'index' from above because we have the correct "name"
                    for ( index in value ) {
                            if ( !( index in props ) ) {
                                    props[ index ] = value[ index ];
                                    specialEasing[ index ] = easing;
                            }
                    }
            } else {
                    specialEasing[ name ] = easing;
            }
    }
}

jQuery.Animation = jQuery.extend( Animation, {

    tweener: function( props, callback ) {
            if ( jQuery.isFunction( props ) ) {
                    callback = props;
                    props = [ "*" ];
            } else {
                    props = props.split(" ");
            }

            var prop,
                    index = 0,
                    length = props.length;

            for ( ; index < length ; index++ ) {
                    prop = props[ index ];
                    tweeners[ prop ] = tweeners[ prop ] || [];
                    tweeners[ prop ].unshift( callback );
            }
    },

    prefilter: function( callback, prepend ) {
            if ( prepend ) {
                    animationPrefilters.unshift( callback );
            } else {
                    animationPrefilters.push( callback );
            }
    }
});

function defaultPrefilter( elem, props, opts ) {
    /* jshint validthis: true */
    var prop, value, toggle, tween, hooks, oldfire,
            anim = this,
            orig = {},
            style = elem.style,
            hidden = elem.nodeType && isHidden( elem ),
            dataShow = data_priv.get( elem, "fxshow" );

    // handle queue: false promises
    if ( !opts.queue ) {
            hooks = jQuery._queueHooks( elem, "fx" );
            if ( hooks.unqueued == null ) {
                    hooks.unqueued = 0;
                    oldfire = hooks.empty.fire;
                    hooks.empty.fire = function() {
                            if ( !hooks.unqueued ) {
                                    oldfire();
                            }
                    };
            }
            hooks.unqueued++;

            anim.always(function() {
                    // doing this makes sure that the complete handler will be called
                    // before this completes
                    anim.always(function() {
                            hooks.unqueued--;
                            if ( !jQuery.queue( elem, "fx" ).length ) {
                                    hooks.empty.fire();
                            }
                    });
            });
    }

    // height/width overflow pass
    if ( elem.nodeType === 1 && ( "height" in props || "width" in props ) ) {
            // Make sure that nothing sneaks out
            // Record all 3 overflow attributes because IE9-10 do not
            // change the overflow attribute when overflowX and
            // overflowY are set to the same value
            opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];

            // Set display property to inline-block for height/width
            // animations on inline elements that are having width/height animated
            if ( jQuery.css( elem, "display" ) === "inline" &&
                            jQuery.css( elem, "float" ) === "none" ) {

                    style.display = "inline-block";
            }
    }

    if ( opts.overflow ) {
            style.overflow = "hidden";
            anim.always(function() {
                    style.overflow = opts.overflow[ 0 ];
                    style.overflowX = opts.overflow[ 1 ];
                    style.overflowY = opts.overflow[ 2 ];
            });
    }


    // show/hide pass
    for ( prop in props ) {
            value = props[ prop ];
            if ( rfxtypes.exec( value ) ) {
                    delete props[ prop ];
                    toggle = toggle || value === "toggle";
                    if ( value === ( hidden ? "hide" : "show" ) ) {

                            // If there is dataShow left over from a stopped hide or show and we are going to proceed with show, we should pretend to be hidden
                            if ( value === "show" && dataShow && dataShow[ prop ] !== undefined ) {
                                    hidden = true;
                            } else {
                                    continue;
                            }
                    }
                    orig[ prop ] = dataShow && dataShow[ prop ] || jQuery.style( elem, prop );
            }
    }

    if ( !jQuery.isEmptyObject( orig ) ) {
            if ( dataShow ) {
                    if ( "hidden" in dataShow ) {
                            hidden = dataShow.hidden;
                    }
            } else {
                    dataShow = data_priv.access( elem, "fxshow", {} );
            }

            // store state if its toggle - enables .stop().toggle() to "reverse"
            if ( toggle ) {
                    dataShow.hidden = !hidden;
            }
            if ( hidden ) {
                    jQuery( elem ).show();
            } else {
                    anim.done(function() {
                            jQuery( elem ).hide();
                    });
            }
            anim.done(function() {
                    var prop;

                    data_priv.remove( elem, "fxshow" );
                    for ( prop in orig ) {
                            jQuery.style( elem, prop, orig[ prop ] );
                    }
            });
            for ( prop in orig ) {
                    tween = createTween( hidden ? dataShow[ prop ] : 0, prop, anim );

                    if ( !( prop in dataShow ) ) {
                            dataShow[ prop ] = tween.start;
                            if ( hidden ) {
                                    tween.end = tween.start;
                                    tween.start = prop === "width" || prop === "height" ? 1 : 0;
                            }
                    }
            }
    }
}

function Tween( elem, options, prop, end, easing ) {
    return new Tween.prototype.init( elem, options, prop, end, easing );
}
jQuery.Tween = Tween;

Tween.prototype = {
    constructor: Tween,
    init: function( elem, options, prop, end, easing, unit ) {
            this.elem = elem;
            this.prop = prop;
            this.easing = easing || "swing";
            this.options = options;
            this.start = this.now = this.cur();
            this.end = end;
            this.unit = unit || ( jQuery.cssNumber[ prop ] ? "" : "px" );
    },
    cur: function() {
            var hooks = Tween.propHooks[ this.prop ];

            return hooks && hooks.get ?
                    hooks.get( this ) :
                    Tween.propHooks._default.get( this );
    },
    run: function( percent ) {
            var eased,
                    hooks = Tween.propHooks[ this.prop ];

            if ( this.options.duration ) {
                    this.pos = eased = jQuery.easing[ this.easing ](
                            percent, this.options.duration * percent, 0, 1, this.options.duration
                    );
            } else {
                    this.pos = eased = percent;
            }
            this.now = ( this.end - this.start ) * eased + this.start;

            if ( this.options.step ) {
                    this.options.step.call( this.elem, this.now, this );
            }

            if ( hooks && hooks.set ) {
                    hooks.set( this );
            } else {
                    Tween.propHooks._default.set( this );
            }
            return this;
    }
};

Tween.prototype.init.prototype = Tween.prototype;

Tween.propHooks = {
    _default: {
            get: function( tween ) {
                    var result;

                    if ( tween.elem[ tween.prop ] != null &&
                            (!tween.elem.style || tween.elem.style[ tween.prop ] == null) ) {
                            return tween.elem[ tween.prop ];
                    }

                    result = jQuery.css( tween.elem, tween.prop, "" );
                    return !result || result === "auto" ? 0 : result;
            },
            set: function( tween ) {
                    if ( jQuery.fx.step[ tween.prop ] ) {
                            jQuery.fx.step[ tween.prop ]( tween );
                    } else if ( tween.elem.style && ( tween.elem.style[ jQuery.cssProps[ tween.prop ] ] != null || jQuery.cssHooks[ tween.prop ] ) ) {
                            jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
                    } else {
                            tween.elem[ tween.prop ] = tween.now;
                    }
            }
    }
};

Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
    set: function( tween ) {
            if ( tween.elem.nodeType && tween.elem.parentNode ) {
                    tween.elem[ tween.prop ] = tween.now;
            }
    }
};

jQuery.each([ "toggle", "show", "hide" ], function( i, name ) {
    var cssFn = jQuery.fn[ name ];
    jQuery.fn[ name ] = function( speed, easing, callback ) {
            return speed == null || typeof speed === "boolean" ?
                    cssFn.apply( this, arguments ) :
                    this.animate( genFx( name, true ), speed, easing, callback );
    };
});

jQuery.fn.extend({
    fadeTo: function( speed, to, easing, callback ) {

            return this.filter( isHidden ).css( "opacity", 0 ).show()

                    .end().animate({ opacity: to }, speed, easing, callback );
    },
    animate: function( prop, speed, easing, callback ) {
            var empty = jQuery.isEmptyObject( prop ),
                    optall = jQuery.speed( speed, easing, callback ),
                    doAnimation = function() {
                            var anim = Animation( this, jQuery.extend( {}, prop ), optall );

                            if ( empty || data_priv.get( this, "finish" ) ) {
                                    anim.stop( true );
                            }
                    };
                    doAnimation.finish = doAnimation;

            return empty || optall.queue === false ?
                    this.each( doAnimation ) :
                    this.queue( optall.queue, doAnimation );
    },
    stop: function( type, clearQueue, gotoEnd ) {
            var stopQueue = function( hooks ) {
                    var stop = hooks.stop;
                    delete hooks.stop;
                    stop( gotoEnd );
            };

            if ( typeof type !== "string" ) {
                    gotoEnd = clearQueue;
                    clearQueue = type;
                    type = undefined;
            }
            if ( clearQueue && type !== false ) {
                    this.queue( type || "fx", [] );
            }

            return this.each(function() {
                    var dequeue = true,
                            index = type != null && type + "queueHooks",
                            timers = jQuery.timers,
                            data = data_priv.get( this );

                    if ( index ) {
                            if ( data[ index ] && data[ index ].stop ) {
                                    stopQueue( data[ index ] );
                            }
                    } else {
                            for ( index in data ) {
                                    if ( data[ index ] && data[ index ].stop && rrun.test( index ) ) {
                                            stopQueue( data[ index ] );
                                    }
                            }
                    }

                    for ( index = timers.length; index--; ) {
                            if ( timers[ index ].elem === this && (type == null || timers[ index ].queue === type) ) {
                                    timers[ index ].anim.stop( gotoEnd );
                                    dequeue = false;
                                    timers.splice( index, 1 );
                            }
                    }

                    if ( dequeue || !gotoEnd ) {
                            jQuery.dequeue( this, type );
                    }
            });
    },
    finish: function( type ) {
            if ( type !== false ) {
                    type = type || "fx";
            }
            return this.each(function() {
                    var index,
                            data = data_priv.get( this ),
                            queue = data[ type + "queue" ],
                            hooks = data[ type + "queueHooks" ],
                            timers = jQuery.timers,
                            length = queue ? queue.length : 0;

                    data.finish = true;
                    jQuery.queue( this, type, [] );

                    if ( hooks && hooks.stop ) {
                            hooks.stop.call( this, true );
                    }
                    for ( index = timers.length; index--; ) {
                            if ( timers[ index ].elem === this && timers[ index ].queue === type ) {
                                    timers[ index ].anim.stop( true );
                                    timers.splice( index, 1 );
                            }
                    }
                    for ( index = 0; index < length; index++ ) {
                            if ( queue[ index ] && queue[ index ].finish ) {
                                    queue[ index ].finish.call( this );
                            }
                    }
                    delete data.finish;
            });
    }
});

function genFx( type, includeWidth ) {
    var which,
            attrs = { height: type },
            i = 0;

    includeWidth = includeWidth? 1 : 0;
    for( ; i < 4 ; i += 2 - includeWidth ) {
            which = cssExpand[ i ];
            attrs[ "margin" + which ] = attrs[ "padding" + which ] = type;
    }

    if ( includeWidth ) {
            attrs.opacity = attrs.width = type;
    }

    return attrs;
}

jQuery.each({
    slideDown: genFx("show"),
    slideUp: genFx("hide"),
    slideToggle: genFx("toggle"),
    fadeIn: { opacity: "show" },
    fadeOut: { opacity: "hide" },
    fadeToggle: { opacity: "toggle" }
}, function( name, props ) {
    jQuery.fn[ name ] = function( speed, easing, callback ) {
            return this.animate( props, speed, easing, callback );
    };
});

jQuery.speed = function( speed, easing, fn ) {
    var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
            complete: fn || !fn && easing ||
                    jQuery.isFunction( speed ) && speed,
            duration: speed,
            easing: fn && easing || easing && !jQuery.isFunction( easing ) && easing
    };

    opt.duration = jQuery.fx.off ? 0 : typeof opt.duration === "number" ? opt.duration :
            opt.duration in jQuery.fx.speeds ? jQuery.fx.speeds[ opt.duration ] : jQuery.fx.speeds._default;

    if ( opt.queue == null || opt.queue === true ) {
            opt.queue = "fx";
    }

    opt.old = opt.complete;

    opt.complete = function() {
            if ( jQuery.isFunction( opt.old ) ) {
                    opt.old.call( this );
            }

            if ( opt.queue ) {
                    jQuery.dequeue( this, opt.queue );
            }
    };

    return opt;
};

jQuery.easing = {
    linear: function( p ) {
            return p;
    },
    swing: function( p ) {
            return 0.5 - Math.cos( p*Math.PI ) / 2;
    }
};

jQuery.timers = [];
jQuery.fx = Tween.prototype.init;
jQuery.fx.tick = function() {
    var timer,
            timers = jQuery.timers,
            i = 0;

    fxNow = jQuery.now();

    for ( ; i < timers.length; i++ ) {
            timer = timers[ i ];
            // Checks the timer has not already been removed
            if ( !timer() && timers[ i ] === timer ) {
                    timers.splice( i--, 1 );
            }
    }

    if ( !timers.length ) {
            jQuery.fx.stop();
    }
    fxNow = undefined;
};

jQuery.fx.timer = function( timer ) {
    if ( timer() && jQuery.timers.push( timer ) ) {
            jQuery.fx.start();
    }
};

jQuery.fx.interval = 13;

jQuery.fx.start = function() {
    if ( !timerId ) {
            timerId = setInterval( jQuery.fx.tick, jQuery.fx.interval );
    }
};

jQuery.fx.stop = function() {
    clearInterval( timerId );
    timerId = null;
};

jQuery.fx.speeds = {
    slow: 600,
    fast: 200,
    // Default speed
    _default: 400
};

jQuery.fx.step = {};

if ( jQuery.expr && jQuery.expr.filters ) {
    jQuery.expr.filters.animated = function( elem ) {
            return jQuery.grep(jQuery.timers, function( fn ) {
                    return elem === fn.elem;
            }).length;
    };
}
jQuery.fn.offset = function( options ) {
    if ( arguments.length ) {
            return options === undefined ?
                    this :
                    this.each(function( i ) {
                            jQuery.offset.setOffset( this, options, i );
                    });
    }

    var docElem, win,
            elem = this[ 0 ],
            box = { top: 0, left: 0 },
            doc = elem && elem.ownerDocument;

    if ( !doc ) {
            return;
    }

    docElem = doc.documentElement;

    if ( !jQuery.contains( docElem, elem ) ) {
            return box;
    }

    if ( typeof elem.getBoundingClientRect !== core_strundefined ) {
            box = elem.getBoundingClientRect();
    }
    win = getWindow( doc );
    return {
            top: box.top + win.pageYOffset - docElem.clientTop,
            left: box.left + win.pageXOffset - docElem.clientLeft
    };
};

jQuery.offset = {

    setOffset: function( elem, options, i ) {
            var curPosition, curLeft, curCSSTop, curTop, curOffset, curCSSLeft, calculatePosition,
                    position = jQuery.css( elem, "position" ),
                    curElem = jQuery( elem ),
                    props = {};

            if ( position === "static" ) {
                    elem.style.position = "relative";
            }

            curOffset = curElem.offset();
            curCSSTop = jQuery.css( elem, "top" );
            curCSSLeft = jQuery.css( elem, "left" );
            calculatePosition = ( position === "absolute" || position === "fixed" ) && ( curCSSTop + curCSSLeft ).indexOf("auto") > -1;

            if ( calculatePosition ) {
                    curPosition = curElem.position();
                    curTop = curPosition.top;
                    curLeft = curPosition.left;

            } else {
                    curTop = parseFloat( curCSSTop ) || 0;
                    curLeft = parseFloat( curCSSLeft ) || 0;
            }

            if ( jQuery.isFunction( options ) ) {
                    options = options.call( elem, i, curOffset );
            }

            if ( options.top != null ) {
                    props.top = ( options.top - curOffset.top ) + curTop;
            }
            if ( options.left != null ) {
                    props.left = ( options.left - curOffset.left ) + curLeft;
            }

            if ( "using" in options ) {
                    options.using.call( elem, props );

            } else {
                    curElem.css( props );
            }
    }
};


jQuery.fn.extend({

    position: function() {
            if ( !this[ 0 ] ) {
                    return;
            }

            var offsetParent, offset,
                    elem = this[ 0 ],
                    parentOffset = { top: 0, left: 0 };

            if ( jQuery.css( elem, "position" ) === "fixed" ) {
                    offset = elem.getBoundingClientRect();

            } else {
                    offsetParent = this.offsetParent();

                    offset = this.offset();
                    if ( !jQuery.nodeName( offsetParent[ 0 ], "html" ) ) {
                            parentOffset = offsetParent.offset();
                    }

                    // Add offsetParent borders
                    parentOffset.top += jQuery.css( offsetParent[ 0 ], "borderTopWidth", true );
                    parentOffset.left += jQuery.css( offsetParent[ 0 ], "borderLeftWidth", true );
            }

            // Subtract parent offsets and element margins
            return {
                    top: offset.top - parentOffset.top - jQuery.css( elem, "marginTop", true ),
                    left: offset.left - parentOffset.left - jQuery.css( elem, "marginLeft", true )
            };
    },

    offsetParent: function() {
            return this.map(function() {
                    var offsetParent = this.offsetParent || docElem;

                    while ( offsetParent && ( !jQuery.nodeName( offsetParent, "html" ) && jQuery.css( offsetParent, "position") === "static" ) ) {
                            offsetParent = offsetParent.offsetParent;
                    }

                    return offsetParent || docElem;
            });
    }
});


jQuery.each( {scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function( method, prop ) {
    var top = "pageYOffset" === prop;

    jQuery.fn[ method ] = function( val ) {
            return jQuery.access( this, function( elem, method, val ) {
                    var win = getWindow( elem );

                    if ( val === undefined ) {
                            return win ? win[ prop ] : elem[ method ];
                    }

                    if ( win ) {
                            win.scrollTo(
                                    !top ? val : window.pageXOffset,
                                    top ? val : window.pageYOffset
                            );

                    } else {
                            elem[ method ] = val;
                    }
            }, method, val, arguments.length, null );
    };
});

function getWindow( elem ) {
    return jQuery.isWindow( elem ) ? elem : elem.nodeType === 9 && elem.defaultView;
}
jQuery.each( { Height: "height", Width: "width" }, function( name, type ) {
    jQuery.each( { padding: "inner" + name, content: type, "": "outer" + name }, function( defaultExtra, funcName ) {
            jQuery.fn[ funcName ] = function( margin, value ) {
                    var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
                            extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );

                    return jQuery.access( this, function( elem, type, value ) {
                            var doc;

                            if ( jQuery.isWindow( elem ) ) {
                                    return elem.document.documentElement[ "client" + name ];
                            }

                            // Get document width or height
                            if ( elem.nodeType === 9 ) {
                                    doc = elem.documentElement;
                                    return Math.max(
                                            elem.body[ "scroll" + name ], doc[ "scroll" + name ],
                                            elem.body[ "offset" + name ], doc[ "offset" + name ],
                                            doc[ "client" + name ]
                                    );
                            }

                            return value === undefined ?
                                    jQuery.css( elem, type, extra ) :
                                    jQuery.style( elem, type, value, extra );
                    }, type, chainable ? margin : undefined, chainable, null );
            };
    });
});
jQuery.fn.size = function() {
    return this.length;
};

jQuery.fn.andSelf = jQuery.fn.addBack;

if ( typeof module === "object" && module && typeof module.exports === "object" ) {
    module.exports = jQuery;
} else {
    if ( typeof define === "function" && define.amd ) {
            define( "jquery", [], function () { return jQuery; } );
    }
}

if ( typeof window === "object" && typeof window.document === "object" ) {
    window.jQuery = window.$ = jQuery;
}

})( window ),
    function()
    {
        function t(t, e, n) {
            for (var i = (n || 0) - 1, r = t ? t.length : 0; ++i < r;)
                if (t[i] === e) return i;
            return -1
        }
        function e(e, n) {
            var i = typeof n;
            if (e = e.cache, "boolean" == i || null == n) return e[n] ? 0 : -1;
            "number" != i && "string" != i && (i = "object");
            var r = "number" == i ? n : y + n;
            return e = (e = e[i]) && e[r], "object" == i ? e && t(e, n) > -1 ? 0 : -1 : e ? 0 : -1
        }
        function n(t) {
            var e = this.cache,
                n = typeof t;
            if ("boolean" == n || null == t) e[t] = !0;
            else {
                "number" != n && "string" != n && (n = "object");
                var i = "number" == n ? t : y + t,
                    r = e[n] || (e[n] = {});
                "object" == n ? (r[i] || (r[i] = [])).push(t) : r[i] = !0
            }
        }
        function i(t) {
            return t.charCodeAt(0)
        }
        function r(t, e) {
            for (var n = t.criteria, i = e.criteria, r = -1, o = n.length; ++r < o;) {
                var a = n[r],
                    s = i[r];
                if (a !== s) {
                    if (a > s || "undefined" == typeof a) return 1;
                    if (s > a || "undefined" == typeof s) return -1
                }
            }
            return t.index - e.index
        }
        function o(t) {
            var e = -1,
                i = t.length,
                r = t[0],
                o = t[i / 2 | 0],
                a = t[i - 1];
            if (r && "object" == typeof r && o && "object" == typeof o && a && "object" == typeof a) return !1;
            var s = u();
            s["false"] = s["null"] = s["true"] = s.undefined = !1;
            var l = u();
            for (l.array = t, l.cache = s, l.push = n; ++e < i;) l.push(t[e]);
            return l
        }
        function a(t) {
            return "\\" + G[t]
        }
        function s() {
            return f.pop() || []
        }
        function u() {
            return m.pop() || {
                array: null,
                cache: null,
                criteria: null,
                "false": !1,
                index: 0,
                "null": !1,
                number: null,
                object: null,
                push: null,
                string: null,
                "true": !1,
                undefined: !1,
                value: null
            }
        }
        function l(t) {
            t.length = 0, f.length < v && f.push(t)
        }
        function c(t) {
            var e = t.cache;
            e && c(e), t.array = t.cache = t.criteria = t.object = t.number = t.string = t.value = null, m.length < v && m.push(t)
        }
        function h(t, e, n) {
            e || (e = 0), "undefined" == typeof n && (n = t ? t.length : 0);
            for (var i = -1, r = n - e || 0, o = Array(0 > r ? 0 : r); ++i < r;) o[i] = t[e + i];
            return o
        }
        function d(n) {
            function f(t) {
                return t && "object" == typeof t && !Jn(t) && In.call(t, "__wrapped__") ? t : new m(t)
            }
            function m(t, e) {
                this.__chain__ = !!e, this.__wrapped__ = t
            }
            function v(t) {
                function e() {
                    if (i) {
                        var t = h(i);
                        Ln.apply(t, arguments)
                    }
                    if (this instanceof e) {
                        var o = Y(n.prototype),
                            a = n.apply(o, t || arguments);
                        return Rt(a) ? a : o
                    }
                    return n.apply(r, t || arguments)
                }
                var n = t[0],
                    i = t[2],
                    r = t[4];
                return Qn(e, t), e
            }
            function G(t, e, n, i, r) {
                if (n) {
                    var o = n(t);
                    if ("undefined" != typeof o) return o
                }
                var a = Rt(t);
                if (!a) return t;
                var u = En.call(t);
                if (!U[u]) return t;
                var c = Yn[u];
                switch (u) {
                    case O:
                    case B:
                        return new c(+t);
                    case $:
                    case V:
                        return new c(t);
                    case z:
                        return o = c(t.source, A.exec(t)), o.lastIndex = t.lastIndex, o
                }
                var d = Jn(t);
                if (e) {
                    var p = !i;
                    i || (i = s()), r || (r = s());
                    for (var f = i.length; f--;)
                        if (i[f] == t) return r[f];
                    o = d ? c(t.length) : {}
                } else o = d ? h(t) : oi({}, t);
                return d && (In.call(t, "index") && (o.index = t.index), In.call(t, "input") && (o.input = t.input)), e ? (i.push(t), r.push(o), (d ? Xt : ui)(t, function(t, a) {
                    o[a] = G(t, e, n, i, r)
                }), p && (l(i), l(r)), o) : o
            }
            function Y(t, e) {
                return Rt(t) ? Hn(t) : {}
            }
            function X(t, e, n) {
                if ("function" != typeof t) return Qe;
                if ("undefined" == typeof e || !("prototype" in t)) return t;
                var i = t.__bindData__;
                if ("undefined" == typeof i && (Xn.funcNames && (i = !t.name), i = i || !Xn.funcDecomp, !i)) {
                    var r = Pn.call(t);
                    Xn.funcNames || (i = !T.test(r)), i || (i = R.test(r), Qn(t, i))
                }
                if (i === !1 || i !== !0 && 1 & i[1]) return t;
                switch (n) {
                    case 1:
                        return function(n) {
                            return t.call(e, n)
                        };
                    case 2:
                        return function(n, i) {
                            return t.call(e, n, i)
                        };
                    case 3:
                        return function(n, i, r) {
                            return t.call(e, n, i, r)
                        };
                    case 4:
                        return function(n, i, r, o) {
                            return t.call(e, n, i, r, o)
                        }
                }
                return Ie(t, e)
            }
            function Q(t) {
                function e() {
                    var t = u ? a : this;
                    if (r) {
                        var f = h(r);
                        Ln.apply(f, arguments)
                    }
                    if ((o || c) && (f || (f = h(arguments)), o && Ln.apply(f, o), c && f.length < s)) return i |= 16, Q([n, d ? i : -4 & i, f, null, a, s]);
                    if (f || (f = arguments), l && (n = t[p]), this instanceof e) {
                        t = Y(n.prototype);
                        var m = n.apply(t, f);
                        return Rt(m) ? m : t
                    }
                    return n.apply(t, f)
                }
                var n = t[0],
                    i = t[1],
                    r = t[2],
                    o = t[3],
                    a = t[4],
                    s = t[5],
                    u = 1 & i,
                    l = 2 & i,
                    c = 4 & i,
                    d = 8 & i,
                    p = n;
                return Qn(e, t), e
            }
            function J(n, i) {
                var r = -1,
                    a = ut(),
                    s = n ? n.length : 0,
                    u = s >= _ && a === t,
                    l = [];
                if (u) {
                    var h = o(i);
                    h ? (a = e, i = h) : u = !1
                }
                for (; ++r < s;) {
                    var d = n[r];
                    a(i, d) < 0 && l.push(d)
                }
                return u && c(i), l
            }
            function tt(t, e, n, i) {
                for (var r = (i || 0) - 1, o = t ? t.length : 0, a = []; ++r < o;) {
                    var s = t[r];
                    if (s && "object" == typeof s && "number" == typeof s.length && (Jn(s) || dt(s))) {
                        e || (s = tt(s, e, n));
                        var u = -1,
                            l = s.length,
                            c = a.length;
                        for (a.length += l; ++u < l;) a[c++] = s[u]
                    } else n || a.push(s)
                }
                return a
            }
            function et(t, e, n, i, r, o) {
                if (n) {
                    var a = n(t, e);
                    if ("undefined" != typeof a) return !!a
                }
                if (t === e) return 0 !== t || 1 / t == 1 / e;
                var u = typeof t,
                    c = typeof e;
                if (!(t !== t || t && q[u] || e && q[c])) return !1;
                if (null == t || null == e) return t === e;
                var h = En.call(t),
                    d = En.call(e);
                if (h == I && (h = H), d == I && (d = H), h != d) return !1;
                switch (h) {
                    case O:
                    case B:
                        return +t == +e;
                    case $:
                        return t != +t ? e != +e : 0 == t ? 1 / t == 1 / e : t == +e;
                    case z:
                    case V:
                        return t == xn(e)
                }
                var p = h == L;
                if (!p) {
                    var f = In.call(t, "__wrapped__"),
                        m = In.call(e, "__wrapped__");
                    if (f || m) return et(f ? t.__wrapped__ : t, m ? e.__wrapped__ : e, n, i, r, o);
                    if (h != H) return !1;
                    var g = t.constructor,
                        y = e.constructor;
                    if (g != y && !(Dt(g) && g instanceof g && Dt(y) && y instanceof y) && "constructor" in t && "constructor" in e) return !1
                }
                var _ = !r;
                r || (r = s()), o || (o = s());
                for (var v = r.length; v--;)
                    if (r[v] == t) return o[v] == e;
                var b = 0;
                if (a = !0, r.push(t), o.push(e), p) {
                    if (v = t.length, b = e.length, a = b == v, a || i)
                        for (; b--;) {
                            var S = v,
                                x = e[b];
                            if (i)
                                for (; S-- && !(a = et(t[S], x, n, i, r, o)););
                            else if (!(a = et(t[b], x, n, i, r, o))) break
                        }
                } else si(e, function(e, s, u) {
                    return In.call(u, s) ? (b++, a = In.call(t, s) && et(t[s], e, n, i, r, o)) : void 0
                }), a && !i && si(t, function(t, e, n) {
                    return In.call(n, e) ? a = --b > -1 : void 0
                });
                return r.pop(), o.pop(), _ && (l(r), l(o)), a
            }
            function nt(t, e, n, i, r) {
                (Jn(e) ? Xt : ui)(e, function(e, o) {
                    var a, s, u = e,
                        l = t[o];
                    if (e && ((s = Jn(e)) || li(e))) {
                        for (var c = i.length; c--;)
                            if (a = i[c] == e) {
                                l = r[c];
                                break
                            }
                        if (!a) {
                            var h;
                            n && (u = n(l, e), (h = "undefined" != typeof u) && (l = u)), h || (l = s ? Jn(l) ? l : [] : li(l) ? l : {}), i.push(e), r.push(l), h || nt(l, e, n, i, r)
                        }
                    } else n && (u = n(l, e), "undefined" == typeof u && (u = e)), "undefined" != typeof u && (l = u);
                    t[o] = l
                })
            }
            function it(t, e) {
                return t + Mn(Kn() * (e - t + 1))
            }
            function rt(n, i, r) {
                var a = -1,
                    u = ut(),
                    h = n ? n.length : 0,
                    d = [],
                    p = !i && h >= _ && u === t,
                    f = r || p ? s() : d;
                if (p) {
                    var m = o(f);
                    u = e, f = m
                }
                for (; ++a < h;) {
                    var g = n[a],
                        y = r ? r(g, a, n) : g;
                    (i ? !a || f[f.length - 1] !== y : u(f, y) < 0) && ((r || p) && f.push(y), d.push(g))
                }
                return p ? (l(f.array), c(f)) : r && l(f), d
            }
            function ot(t) {
                return function(e, n, i) {
                    var r = {};
                    n = f.createCallback(n, i, 3);
                    var o = -1,
                        a = e ? e.length : 0;
                    if ("number" == typeof a)
                        for (; ++o < a;) {
                            var s = e[o];
                            t(r, s, n(s, o, e), e)
                        } else ui(e, function(e, i, o) {
                            t(r, e, n(e, i, o), o)
                        });
                    return r
                }
            }
            function at(t, e, n, i, r, o) {
                var a = 1 & e,
                    s = 2 & e,
                    u = 4 & e,
                    l = 16 & e,
                    c = 32 & e;
                if (!s && !Dt(t)) throw new wn;
                l && !n.length && (e &= -17, l = n = !1), c && !i.length && (e &= -33, c = i = !1);
                var d = t && t.__bindData__;
                if (d && d !== !0) return d = h(d), d[2] && (d[2] = h(d[2])), d[3] && (d[3] = h(d[3])), !a || 1 & d[1] || (d[4] = r), !a && 1 & d[1] && (e |= 8), !u || 4 & d[1] || (d[5] = o), l && Ln.apply(d[2] || (d[2] = []), n), c && Fn.apply(d[3] || (d[3] = []), i), d[1] |= e, at.apply(null, d);
                var p = 1 == e || 17 === e ? v : Q;
                return p([t, e, n, i, r, o])
            }
            function st(t) {
                return ei[t]
            }
            function ut() {
                var e = (e = f.indexOf) === ye ? t : e;
                return e
            }
            function lt(t) {
                return "function" == typeof t && kn.test(t)
            }
            function ct(t) {
                var e, n;
                return t && En.call(t) == H && (e = t.constructor, !Dt(e) || e instanceof e) ? (si(t, function(t, e) {
                    n = e
                }), "undefined" == typeof n || In.call(t, n)) : !1
            }
            function ht(t) {
                return ni[t]
            }
            function dt(t) {
                return t && "object" == typeof t && "number" == typeof t.length && En.call(t) == I || !1
            }
            function pt(t, e, n, i) {
                return "boolean" != typeof e && null != e && (i = n, n = e, e = !1), G(t, e, "function" == typeof n && X(n, i, 1))
            }
            function ft(t, e, n) {
                return G(t, !0, "function" == typeof e && X(e, n, 1))
            }
            function mt(t, e) {
                var n = Y(t);
                return e ? oi(n, e) : n
            }
            function gt(t, e, n) {
                var i;
                return e = f.createCallback(e, n, 3), ui(t, function(t, n, r) {
                    return e(t, n, r) ? (i = n, !1) : void 0
                }), i
            }
            function yt(t, e, n) {
                var i;
                return e = f.createCallback(e, n, 3), vt(t, function(t, n, r) {
                    return e(t, n, r) ? (i = n, !1) : void 0
                }), i
            }
            function _t(t, e, n) {
                var i = [];
                si(t, function(t, e) {
                    i.push(e, t)
                });
                var r = i.length;
                for (e = X(e, n, 3); r-- && e(i[r--], i[r], t) !== !1;);
                return t
            }
            function vt(t, e, n) {
                var i = ti(t),
                    r = i.length;
                for (e = X(e, n, 3); r--;) {
                    var o = i[r];
                    if (e(t[o], o, t) === !1) break
                }
                return t
            }
            function bt(t) {
                var e = [];
                return si(t, function(t, n) {
                    Dt(t) && e.push(n)
                }), e.sort()
            }
            function St(t, e) {
                return t ? In.call(t, e) : !1
            }
            function xt(t) {
                for (var e = -1, n = ti(t), i = n.length, r = {}; ++e < i;) {
                    var o = n[e];
                    r[t[o]] = o
                }
                return r
            }
            function wt(t) {
                return t === !0 || t === !1 || t && "object" == typeof t && En.call(t) == O || !1
            }
            function Ct(t) {
                return t && "object" == typeof t && En.call(t) == B || !1
            }
            function At(t) {
                return t && 1 === t.nodeType || !1
            }
            function Tt(t) {
                var e = !0;
                if (!t) return e;
                var n = En.call(t),
                    i = t.length;
                return n == L || n == V || n == I || n == H && "number" == typeof i && Dt(t.splice) ? !i : (ui(t, function() {
                    return e = !1
                }), e)
            }
            function Et(t, e, n, i) {
                return et(t, e, "function" == typeof n && X(n, i, 2))
            }
            function kt(t) {
                return Vn(t) && !Un(parseFloat(t))
            }
            function Dt(t) {
                return "function" == typeof t
            }
            function Rt(t) {
                return !(!t || !q[typeof t])
            }
            function Mt(t) {
                return Nt(t) && t != +t
            }
            function Pt(t) {
                return null === t
            }
            function Nt(t) {
                return "number" == typeof t || t && "object" == typeof t && En.call(t) == $ || !1
            }
            function It(t) {
                return t && "object" == typeof t && En.call(t) == z || !1
            }
            function Lt(t) {
                return "string" == typeof t || t && "object" == typeof t && En.call(t) == V || !1
            }
            function Ot(t) {
                return "undefined" == typeof t
            }
            function Bt(t, e, n) {
                var i = {};
                return e = f.createCallback(e, n, 3), ui(t, function(t, n, r) {
                    i[n] = e(t, n, r)
                }), i
            }
            function Ft(t) {
                var e = arguments,
                    n = 2;
                if (!Rt(t)) return t;
                if ("number" != typeof e[2] && (n = e.length), n > 3 && "function" == typeof e[n - 2]) var i = X(e[--n - 1], e[n--], 2);
                else n > 2 && "function" == typeof e[n - 1] && (i = e[--n]);
                for (var r = h(arguments, 1, n), o = -1, a = s(), u = s(); ++o < n;) nt(t, r[o], i, a, u);
                return l(a), l(u), t
            }
            function $t(t, e, n) {
                var i = {};
                if ("function" != typeof e) {
                    var r = [];
                    si(t, function(t, e) {
                        r.push(e)
                    }), r = J(r, tt(arguments, !0, !1, 1));
                    for (var o = -1, a = r.length; ++o < a;) {
                        var s = r[o];
                        i[s] = t[s]
                    }
                } else e = f.createCallback(e, n, 3), si(t, function(t, n, r) {
                    e(t, n, r) || (i[n] = t)
                });
                return i
            }
            function Ht(t) {
                for (var e = -1, n = ti(t), i = n.length, r = fn(i); ++e < i;) {
                    var o = n[e];
                    r[e] = [o, t[o]]
                }
                return r
            }
            function zt(t, e, n) {
                var i = {};
                if ("function" != typeof e)
                    for (var r = -1, o = tt(arguments, !0, !1, 1), a = Rt(t) ? o.length : 0; ++r < a;) {
                        var s = o[r];
                        s in t && (i[s] = t[s])
                    } else e = f.createCallback(e, n, 3), si(t, function(t, n, r) {
                        e(t, n, r) && (i[n] = t)
                    });
                return i
            }
            function Vt(t, e, n, i) {
                var r = Jn(t);
                if (null == n)
                    if (r) n = [];
                    else {
                        var o = t && t.constructor,
                            a = o && o.prototype;
                        n = Y(a)
                    }
                return e && (e = f.createCallback(e, i, 4), (r ? Xt : ui)(t, function(t, i, r) {
                    return e(n, t, i, r)
                })), n
            }
            function Ut(t) {
                for (var e = -1, n = ti(t), i = n.length, r = fn(i); ++e < i;) r[e] = t[n[e]];
                return r
            }
            function jt(t) {
                for (var e = arguments, n = -1, i = tt(e, !0, !1, 1), r = e[2] && e[2][e[1]] === t ? 1 : i.length, o = fn(r); ++n < r;) o[n] = t[i[n]];
                return o
            }
            function Wt(t, e, n) {
                var i = -1,
                    r = ut(),
                    o = t ? t.length : 0,
                    a = !1;
                return n = (0 > n ? Wn(0, o + n) : n) || 0, Jn(t) ? a = r(t, e, n) > -1 : "number" == typeof o ? a = (Lt(t) ? t.indexOf(e, n) : r(t, e, n)) > -1 : ui(t, function(t) {
                    return ++i >= n ? !(a = t === e) : void 0
                }), a
            }
            function qt(t, e, n) {
                var i = !0;
                e = f.createCallback(e, n, 3);
                var r = -1,
                    o = t ? t.length : 0;
                if ("number" == typeof o)
                    for (; ++r < o && (i = !!e(t[r], r, t)););
                else ui(t, function(t, n, r) {
                    return i = !!e(t, n, r)
                });
                return i
            }
            function Gt(t, e, n) {
                var i = [];
                e = f.createCallback(e, n, 3);
                var r = -1,
                    o = t ? t.length : 0;
                if ("number" == typeof o)
                    for (; ++r < o;) {
                        var a = t[r];
                        e(a, r, t) && i.push(a)
                    } else ui(t, function(t, n, r) {
                        e(t, n, r) && i.push(t)
                    });
                return i
            }
            function Kt(t, e, n) {
                e = f.createCallback(e, n, 3);
                var i = -1,
                    r = t ? t.length : 0;
                if ("number" != typeof r) {
                    var o;
                    return ui(t, function(t, n, i) {
                        return e(t, n, i) ? (o = t, !1) : void 0
                    }), o
                }
                for (; ++i < r;) {
                    var a = t[i];
                    if (e(a, i, t)) return a
                }
            }
            function Yt(t, e, n) {
                var i;
                return e = f.createCallback(e, n, 3), Qt(t, function(t, n, r) {
                    return e(t, n, r) ? (i = t, !1) : void 0
                }), i
            }
            function Xt(t, e, n) {
                var i = -1,
                    r = t ? t.length : 0;
                if (e = e && "undefined" == typeof n ? e : X(e, n, 3), "number" == typeof r)
                    for (; ++i < r && e(t[i], i, t) !== !1;);
                else ui(t, e);
                return t
            }
            function Qt(t, e, n) {
                var i = t ? t.length : 0;
                if (e = e && "undefined" == typeof n ? e : X(e, n, 3), "number" == typeof i)
                    for (; i-- && e(t[i], i, t) !== !1;);
                else {
                    var r = ti(t);
                    i = r.length, ui(t, function(t, n, o) {
                        return n = r ? r[--i] : --i, e(o[n], n, o)
                    })
                }
                return t
            }
            function Jt(t, e) {
                var n = h(arguments, 2),
                    i = -1,
                    r = "function" == typeof e,
                    o = t ? t.length : 0,
                    a = fn("number" == typeof o ? o : 0);
                return Xt(t, function(t) {
                    a[++i] = (r ? e : t[e]).apply(t, n)
                }), a
            }
            function Zt(t, e, n) {
                var i = -1,
                    r = t ? t.length : 0;
                if (e = f.createCallback(e, n, 3), "number" == typeof r)
                    for (var o = fn(r); ++i < r;) o[i] = e(t[i], i, t);
                else o = [], ui(t, function(t, n, r) {
                    o[++i] = e(t, n, r)
                });
                return o
            }
            function te(t, e, n) {
                var r = -(1 / 0),
                    o = r;
                if ("function" != typeof e && n && n[e] === t && (e = null), null == e && Jn(t))
                    for (var a = -1, s = t.length; ++a < s;) {
                        var u = t[a];
                        u > o && (o = u)
                    } else e = null == e && Lt(t) ? i : f.createCallback(e, n, 3), Xt(t, function(t, n, i) {
                        var a = e(t, n, i);
                        a > r && (r = a, o = t)
                    });
                return o
            }
            function ee(t, e, n) {
                var r = 1 / 0,
                    o = r;
                if ("function" != typeof e && n && n[e] === t && (e = null), null == e && Jn(t))
                    for (var a = -1, s = t.length; ++a < s;) {
                        var u = t[a];
                        o > u && (o = u)
                    } else e = null == e && Lt(t) ? i : f.createCallback(e, n, 3), Xt(t, function(t, n, i) {
                        var a = e(t, n, i);
                        r > a && (r = a, o = t)
                    });
                return o
            }
            function ne(t, e, n, i) {
                if (!t) return n;
                var r = arguments.length < 3;
                e = f.createCallback(e, i, 4);
                var o = -1,
                    a = t.length;
                if ("number" == typeof a)
                    for (r && (n = t[++o]); ++o < a;) n = e(n, t[o], o, t);
                else ui(t, function(t, i, o) {
                    n = r ? (r = !1, t) : e(n, t, i, o)
                });
                return n
            }
            function ie(t, e, n, i) {
                var r = arguments.length < 3;
                return e = f.createCallback(e, i, 4), Qt(t, function(t, i, o) {
                    n = r ? (r = !1, t) : e(n, t, i, o)
                }), n
            }
            function re(t, e, n) {
                return e = f.createCallback(e, n, 3), Gt(t, function(t, n, i) {
                    return !e(t, n, i)
                })
            }
            function oe(t, e, n) {
                if (t && "number" != typeof t.length && (t = Ut(t)), null == e || n) return t ? t[it(0, t.length - 1)] : p;
                var i = ae(t);
                return i.length = qn(Wn(0, e), i.length), i
            }
            function ae(t) {
                var e = -1,
                    n = t ? t.length : 0,
                    i = fn("number" == typeof n ? n : 0);
                return Xt(t, function(t) {
                    var n = it(0, ++e);
                    i[e] = i[n], i[n] = t
                }), i
            }
            function se(t) {
                var e = t ? t.length : 0;
                return "number" == typeof e ? e : ti(t).length
            }
            function ue(t, e, n) {
                var i;
                e = f.createCallback(e, n, 3);
                var r = -1,
                    o = t ? t.length : 0;
                if ("number" == typeof o)
                    for (; ++r < o && !(i = e(t[r], r, t)););
                else ui(t, function(t, n, r) {
                    return !(i = e(t, n, r))
                });
                return !!i
            }
            function le(t, e, n) {
                var i = -1,
                    o = Jn(e),
                    a = t ? t.length : 0,
                    h = fn("number" == typeof a ? a : 0);
                for (o || (e = f.createCallback(e, n, 3)), Xt(t, function(t, n, r) {
                        var a = h[++i] = u();
                        o ? a.criteria = Zt(e, function(e) {
                            return t[e]
                        }) : (a.criteria = s())[0] = e(t, n, r), a.index = i, a.value = t
                    }), a = h.length, h.sort(r); a--;) {
                    var d = h[a];
                    h[a] = d.value, o || l(d.criteria), c(d)
                }
                return h
            }
            function ce(t) {
                return t && "number" == typeof t.length ? h(t) : Ut(t)
            }
            function he(t) {
                for (var e = -1, n = t ? t.length : 0, i = []; ++e < n;) {
                    var r = t[e];
                    r && i.push(r)
                }
                return i
            }
            function de(t) {
                return J(t, tt(arguments, !0, !0, 1))
            }
            function pe(t, e, n) {
                var i = -1,
                    r = t ? t.length : 0;
                for (e = f.createCallback(e, n, 3); ++i < r;)
                    if (e(t[i], i, t)) return i;
                return -1
            }
            function fe(t, e, n) {
                var i = t ? t.length : 0;
                for (e = f.createCallback(e, n, 3); i--;)
                    if (e(t[i], i, t)) return i;
                return -1
            }
            function me(t, e, n) {
                var i = 0,
                    r = t ? t.length : 0;
                if ("number" != typeof e && null != e) {
                    var o = -1;
                    for (e = f.createCallback(e, n, 3); ++o < r && e(t[o], o, t);) i++
                } else if (i = e, null == i || n) return t ? t[0] : p;
                return h(t, 0, qn(Wn(0, i), r))
            }
            function ge(t, e, n, i) {
                return "boolean" != typeof e && null != e && (i = n, n = "function" != typeof e && i && i[e] === t ? null : e, e = !1), null != n && (t = Zt(t, n, i)), tt(t, e)
            }
            function ye(e, n, i) {
                if ("number" == typeof i) {
                    var r = e ? e.length : 0;
                    i = 0 > i ? Wn(0, r + i) : i || 0
                } else if (i) {
                    var o = Te(e, n);
                    return e[o] === n ? o : -1
                }
                return t(e, n, i)
            }
            function _e(t, e, n) {
                var i = 0,
                    r = t ? t.length : 0;
                if ("number" != typeof e && null != e) {
                    var o = r;
                    for (e = f.createCallback(e, n, 3); o-- && e(t[o], o, t);) i++
                } else i = null == e || n ? 1 : e || i;
                return h(t, 0, qn(Wn(0, r - i), r))
            }
            function ve() {
                for (var n = [], i = -1, r = arguments.length, a = s(), u = ut(), h = u === t, d = s(); ++i < r;) {
                    var p = arguments[i];
                    (Jn(p) || dt(p)) && (n.push(p), a.push(h && p.length >= _ && o(i ? n[i] : d)))
                }
                var f = n[0],
                    m = -1,
                    g = f ? f.length : 0,
                    y = [];
                t: for (; ++m < g;) {
                    var v = a[0];
                    if (p = f[m], (v ? e(v, p) : u(d, p)) < 0) {
                        for (i = r, (v || d).push(p); --i;)
                            if (v = a[i], (v ? e(v, p) : u(n[i], p)) < 0) continue t;
                        y.push(p)
                    }
                }
                for (; r--;) v = a[r], v && c(v);
                return l(a), l(d), y
            }
            function be(t, e, n) {
                var i = 0,
                    r = t ? t.length : 0;
                if ("number" != typeof e && null != e) {
                    var o = r;
                    for (e = f.createCallback(e, n, 3); o-- && e(t[o], o, t);) i++
                } else if (i = e, null == i || n) return t ? t[r - 1] : p;
                return h(t, Wn(0, r - i))
            }
            function Se(t, e, n) {
                var i = t ? t.length : 0;
                for ("number" == typeof n && (i = (0 > n ? Wn(0, i + n) : qn(n, i - 1)) + 1); i--;)
                    if (t[i] === e) return i;
                return -1
            }
            function xe(t) {
                for (var e = arguments, n = 0, i = e.length, r = t ? t.length : 0; ++n < i;)
                    for (var o = -1, a = e[n]; ++o < r;) t[o] === a && (Bn.call(t, o--, 1), r--);
                return t
            }
            function we(t, e, n) {
                t = +t || 0, n = "number" == typeof n ? n : +n || 1, null == e && (e = t, t = 0);
                for (var i = -1, r = Wn(0, Dn((e - t) / (n || 1))), o = fn(r); ++i < r;) o[i] = t, t += n;
                return o
            }
            function Ce(t, e, n) {
                var i = -1,
                    r = t ? t.length : 0,
                    o = [];
                for (e = f.createCallback(e, n, 3); ++i < r;) {
                    var a = t[i];
                    e(a, i, t) && (o.push(a), Bn.call(t, i--, 1), r--)
                }
                return o
            }
            function Ae(t, e, n) {
                if ("number" != typeof e && null != e) {
                    var i = 0,
                        r = -1,
                        o = t ? t.length : 0;
                    for (e = f.createCallback(e, n, 3); ++r < o && e(t[r], r, t);) i++
                } else i = null == e || n ? 1 : Wn(0, e);
                return h(t, i)
            }
            function Te(t, e, n, i) {
                var r = 0,
                    o = t ? t.length : r;
                for (n = n ? f.createCallback(n, i, 1) : Qe, e = n(e); o > r;) {
                    var a = r + o >>> 1;
                    n(t[a]) < e ? r = a + 1 : o = a
                }
                return r
            }
            function Ee() {
                return rt(tt(arguments, !0, !0))
            }
            function ke(t, e, n, i) {
                return "boolean" != typeof e && null != e && (i = n, n = "function" != typeof e && i && i[e] === t ? null : e, e = !1), null != n && (n = f.createCallback(n, i, 3)), rt(t, e, n)
            }
            function De(t) {
                return J(t, h(arguments, 1))
            }
            function Re() {
                for (var t = -1, e = arguments.length; ++t < e;) {
                    var n = arguments[t];
                    if (Jn(n) || dt(n)) var i = i ? rt(J(i, n).concat(J(n, i))) : n
                }
                return i || []
            }
            function Me() {
                for (var t = arguments.length > 1 ? arguments : arguments[0], e = -1, n = t ? te(pi(t, "length")) : 0, i = fn(0 > n ? 0 : n); ++e < n;) i[e] = pi(t, e);
                return i
            }
            function Pe(t, e) {
                var n = -1,
                    i = t ? t.length : 0,
                    r = {};
                for (e || !i || Jn(t[0]) || (e = []); ++n < i;) {
                    var o = t[n];
                    e ? r[o] = e[n] : o && (r[o[0]] = o[1])
                }
                return r
            }
            function Ne(t, e) {
                if (!Dt(e)) throw new wn;
                return function() {
                    return --t < 1 ? e.apply(this, arguments) : void 0
                }
            }
            function Ie(t, e) {
                return arguments.length > 2 ? at(t, 17, h(arguments, 2), null, e) : at(t, 1, null, null, e)
            }
            function Le(t) {
                for (var e = arguments.length > 1 ? tt(arguments, !0, !1, 1) : bt(t), n = -1, i = e.length; ++n < i;) {
                    var r = e[n];
                    t[r] = at(t[r], 1, null, null, t)
                }
                return t
            }
            function Oe(t, e) {
                return arguments.length > 2 ? at(e, 19, h(arguments, 2), null, t) : at(e, 3, null, null, t)
            }
            function Be() {
                for (var t = arguments, e = t.length; e--;)
                    if (!Dt(t[e])) throw new wn;
                return function() {
                    for (var e = arguments, n = t.length; n--;) e = [t[n].apply(this, e)];
                    return e[0]
                }
            }
            function Fe(t, e) {
                return e = "number" == typeof e ? e : +e || t.length, at(t, 4, null, null, null, e)
            }
            function $e(t, e, n) {
                var i, r, o, a, s, u, l, c = 0,
                    h = !1,
                    d = !0;
                if (!Dt(t)) throw new wn;
                if (e = Wn(0, e) || 0, n === !0) {
                    var f = !0;
                    d = !1
                } else Rt(n) && (f = n.leading, h = "maxWait" in n && (Wn(e, n.maxWait) || 0), d = "trailing" in n ? n.trailing : d);
                var m = function() {
                        var n = e - (mi() - a);
                        if (0 >= n) {
                            r && Rn(r);
                            var h = l;
                            r = u = l = p, h && (c = mi(), o = t.apply(s, i), u || r || (i = s = null))
                        } else u = On(m, n)
                    },
                    g = function() {
                        u && Rn(u), r = u = l = p, (d || h !== e) && (c = mi(), o = t.apply(s, i), u || r || (i = s = null))
                    };
                return function() {
                    if (i = arguments, a = mi(), s = this, l = d && (u || !f), h === !1) var n = f && !u;
                    else {
                        r || f || (c = a);
                        var p = h - (a - c),
                            y = 0 >= p;
                        y ? (r && (r = Rn(r)), c = a, o = t.apply(s, i)) : r || (r = On(g, p))
                    }
                    return y && u ? u = Rn(u) : u || e === h || (u = On(m, e)), n && (y = !0, o = t.apply(s, i)), !y || u || r || (i = s = null), o
                }
            }
            function He(t) {
                if (!Dt(t)) throw new wn;
                var e = h(arguments, 1);
                return On(function() {
                    t.apply(p, e)
                }, 1)
            }
            function ze(t, e) {
                if (!Dt(t)) throw new wn;
                var n = h(arguments, 2);
                return On(function() {
                    t.apply(p, n)
                }, e)
            }
            function Ve(t, e) {
                if (!Dt(t)) throw new wn;
                var n = function() {
                    var i = n.cache,
                        r = e ? e.apply(this, arguments) : y + arguments[0];
                    return In.call(i, r) ? i[r] : i[r] = t.apply(this, arguments)
                };
                return n.cache = {}, n
            }
            function Ue(t) {
                var e, n;
                if (!Dt(t)) throw new wn;
                return function() {
                    return e ? n : (e = !0, n = t.apply(this, arguments), t = null, n)
                }
            }
            function je(t) {
                return at(t, 16, h(arguments, 1))
            }
            function We(t) {
                return at(t, 32, null, h(arguments, 1))
            }
            function qe(t, e, n) {
                var i = !0,
                    r = !0;
                if (!Dt(t)) throw new wn;
                return n === !1 ? i = !1 : Rt(n) && (i = "leading" in n ? n.leading : i, r = "trailing" in n ? n.trailing : r), j.leading = i, j.maxWait = e, j.trailing = r, $e(t, e, j)
            }
            function Ge(t, e) {
                return at(e, 16, [t])
            }
            function Ke(t) {
                return function() {
                    return t
                }
            }
            function Ye(t, e, n) {
                var i = typeof t;
                if (null == t || "function" == i) return X(t, e, n);
                if ("object" != i) return en(t);
                var r = ti(t),
                    o = r[0],
                    a = t[o];
                return 1 != r.length || a !== a || Rt(a) ? function(e) {
                    for (var n = r.length, i = !1; n-- && (i = et(e[r[n]], t[r[n]], null, !0)););
                    return i
                } : function(t) {
                    var e = t[o];
                    return a === e && (0 !== a || 1 / a == 1 / e)
                }
            }
            function Xe(t) {
                return null == t ? "" : xn(t).replace(ri, st)
            }
            function Qe(t) {
                return t
            }
            function Je(t, e, n) {
                var i = !0,
                    r = e && bt(e);
                e && (n || r.length) || (null == n && (n = e), o = m, e = t, t = f, r = bt(e)), n === !1 ? i = !1 : Rt(n) && "chain" in n && (i = n.chain);
                var o = t,
                    a = Dt(o);
                Xt(r, function(n) {
                    var r = t[n] = e[n];
                    a && (o.prototype[n] = function() {
                        var e = this.__chain__,
                            n = this.__wrapped__,
                            a = [n];
                        Ln.apply(a, arguments);
                        var s = r.apply(t, a);
                        if (i || e) {
                            if (n === s && Rt(s)) return this;
                            s = new o(s), s.__chain__ = e
                        }
                        return s
                    })
                })
            }
            function Ze() {
                return n._ = Tn, this
            }
            function tn() {}
            function en(t) {
                return function(e) {
                    return e[t]
                }
            }
            function nn(t, e, n) {
                var i = null == t,
                    r = null == e;
                if (null == n && ("boolean" == typeof t && r ? (n = t, t = 1) : r || "boolean" != typeof e || (n = e, r = !0)), i && r && (e = 1), t = +t || 0, r ? (e = t, t = 0) : e = +e || 0, n || t % 1 || e % 1) {
                    var o = Kn();
                    return qn(t + o * (e - t + parseFloat("1e-" + ((o + "").length - 1))), e)
                }
                return it(t, e)
            }
            function rn(t, e) {
                if (t) {
                    var n = t[e];
                    return Dt(n) ? t[e]() : n
                }
            }
            function on(t, e, n) {
                var i = f.templateSettings;
                t = xn(t || ""), n = ai({}, n, i);
                var r, o = ai({}, n.imports, i.imports),
                    s = ti(o),
                    u = Ut(o),
                    l = 0,
                    c = n.interpolate || D,
                    h = "__p += '",
                    d = Sn((n.escape || D).source + "|" + c.source + "|" + (c === E ? C : D).source + "|" + (n.evaluate || D).source + "|$", "g");
                t.replace(d, function(e, n, i, o, s, u) {
                    return i || (i = o), h += t.slice(l, u).replace(M, a), n && (h += "' +\n__e(" + n + ") +\n'"), s && (r = !0, h += "';\n" + s + ";\n__p += '"), i && (h += "' +\n((__t = (" + i + ")) == null ? '' : __t) +\n'"), l = u + e.length, e
                }), h += "';\n";
                var m = n.variable,
                    g = m;
                g || (m = "obj", h = "with (" + m + ") {\n" + h + "\n}\n"), h = (r ? h.replace(S, "") : h).replace(x, "$1").replace(w, "$1;"), h = "function(" + m + ") {\n" + (g ? "" : m + " || (" + m + " = {});\n") + "var __t, __p = '', __e = _.escape" + (r ? ", __j = Array.prototype.join;\nfunction print() { __p += __j.call(arguments, '') }\n" : ";\n") + h + "return __p\n}";
                var y = "\n/*\n//# sourceURL=" + (n.sourceURL || "/lodash/template/source[" + N++ + "]") + "\n*/";
                try {
                    var _ = yn(s, "return " + h + y).apply(p, u)
                } catch (v) {
                    throw v.source = h, v
                }
                return e ? _(e) : (_.source = h, _)
            }
            function an(t, e, n) {
                t = (t = +t) > -1 ? t : 0;
                var i = -1,
                    r = fn(t);
                for (e = X(e, n, 1); ++i < t;) r[i] = e(i);
                return r
            }
            function sn(t) {
                return null == t ? "" : xn(t).replace(ii, ht)
            }
            function un(t) {
                var e = ++g;
                return xn(null == t ? "" : t) + e
            }
            function ln(t) {
                return t = new m(t), t.__chain__ = !0, t
            }
            function cn(t, e) {
                return e(t), t
            }
            function hn() {
                return this.__chain__ = !0, this
            }
            function dn() {
                return xn(this.__wrapped__)
            }
            function pn() {
                return this.__wrapped__
            }
            n = n ? Z.defaults(K.Object(), n, Z.pick(K, P)) : K;
            var fn = n.Array,
                mn = n.Boolean,
                gn = n.Date,
                yn = n.Function,
                _n = n.Math,
                vn = n.Number,
                bn = n.Object,
                Sn = n.RegExp,
                xn = n.String,
                wn = n.TypeError,
                Cn = [],
                An = bn.prototype,
                Tn = n._,
                En = An.toString,
                kn = Sn("^" + xn(En).replace(/[.*+?^${}()|[\]\\]/g, "\\$&").replace(/toString| for [^\]]+/g, ".*?") + "$"),
                Dn = _n.ceil,
                Rn = n.clearTimeout,
                Mn = _n.floor,
                Pn = yn.prototype.toString,
                Nn = lt(Nn = bn.getPrototypeOf) && Nn,
                In = An.hasOwnProperty,
                Ln = Cn.push,
                On = n.setTimeout,
                Bn = Cn.splice,
                Fn = Cn.unshift,
                $n = function() {
                    try {
                        var t = {},
                            e = lt(e = bn.defineProperty) && e,
                            n = e(t, t, t) && e
                    } catch (i) {}
                    return n
                }(),
                Hn = lt(Hn = bn.create) && Hn,
                zn = lt(zn = fn.isArray) && zn,
                Vn = n.isFinite,
                Un = n.isNaN,
                jn = lt(jn = bn.keys) && jn,
                Wn = _n.max,
                qn = _n.min,
                Gn = n.parseInt,
                Kn = _n.random,
                Yn = {};
            Yn[L] = fn, Yn[O] = mn, Yn[B] = gn, Yn[F] = yn, Yn[H] = bn, Yn[$] = vn, Yn[z] = Sn, Yn[V] = xn, m.prototype = f.prototype;
            var Xn = f.support = {};
            Xn.funcDecomp = !lt(n.WinRTError) && R.test(d), Xn.funcNames = "string" == typeof yn.name, f.templateSettings = {
                escape: /<%-([\s\S]+?)%>/g,
                evaluate: /<%([\s\S]+?)%>/g,
                interpolate: E,
                variable: "",
                imports: {
                    _: f
                }
            }, Hn || (Y = function() {
                function t() {}
                return function(e) {
                    if (Rt(e)) {
                        t.prototype = e;
                        var i = new t;
                        t.prototype = null
                    }
                    return i || n.Object()
                }
            }());
            var Qn = $n ? function(t, e) {
                    W.value = e, $n(t, "__bindData__", W)
                } : tn,
                Jn = zn || function(t) {
                    return t && "object" == typeof t && "number" == typeof t.length && En.call(t) == L || !1
                },
                Zn = function(t) {
                    var e, n = t,
                        i = [];
                    if (!n) return i;
                    if (!q[typeof t]) return i;
                    for (e in n) In.call(n, e) && i.push(e);
                    return i
                },
                ti = jn ? function(t) {
                    return Rt(t) ? jn(t) : []
                } : Zn,
                ei = {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;"
                },
                ni = xt(ei),
                ii = Sn("(" + ti(ni).join("|") + ")", "g"),
                ri = Sn("[" + ti(ei).join("") + "]", "g"),
                oi = function(t, e, n) {
                    var i, r = t,
                        o = r;
                    if (!r) return o;
                    var a = arguments,
                        s = 0,
                        u = "number" == typeof n ? 2 : a.length;
                    if (u > 3 && "function" == typeof a[u - 2]) var l = X(a[--u - 1], a[u--], 2);
                    else u > 2 && "function" == typeof a[u - 1] && (l = a[--u]);
                    for (; ++s < u;)
                        if (r = a[s], r && q[typeof r])
                            for (var c = -1, h = q[typeof r] && ti(r), d = h ? h.length : 0; ++c < d;) i = h[c], o[i] = l ? l(o[i], r[i]) : r[i];
                    return o
                },
                ai = function(t, e, n) {
                    var i, r = t,
                        o = r;
                    if (!r) return o;
                    for (var a = arguments, s = 0, u = "number" == typeof n ? 2 : a.length; ++s < u;)
                        if (r = a[s], r && q[typeof r])
                            for (var l = -1, c = q[typeof r] && ti(r), h = c ? c.length : 0; ++l < h;) i = c[l], "undefined" == typeof o[i] && (o[i] = r[i]);
                    return o
                },
                si = function(t, e, n) {
                    var i, r = t,
                        o = r;
                    if (!r) return o;
                    if (!q[typeof r]) return o;
                    e = e && "undefined" == typeof n ? e : X(e, n, 3);
                    for (i in r)
                        if (e(r[i], i, t) === !1) return o;
                    return o
                },
                ui = function(t, e, n) {
                    var i, r = t,
                        o = r;
                    if (!r) return o;
                    if (!q[typeof r]) return o;
                    e = e && "undefined" == typeof n ? e : X(e, n, 3);
                    for (var a = -1, s = q[typeof r] && ti(r), u = s ? s.length : 0; ++a < u;)
                        if (i = s[a], e(r[i], i, t) === !1) return o;
                    return o
                },
                li = Nn ? function(t) {
                    if (!t || En.call(t) != H) return !1;
                    var e = t.valueOf,
                        n = lt(e) && (n = Nn(e)) && Nn(n);
                    return n ? t == n || Nn(t) == n : ct(t)
                } : ct,
                ci = ot(function(t, e, n) {
                    In.call(t, n) ? t[n]++ : t[n] = 1
                }),
                hi = ot(function(t, e, n) {
                    (In.call(t, n) ? t[n] : t[n] = []).push(e)
                }),
                di = ot(function(t, e, n) {
                    t[n] = e
                }),
                pi = Zt,
                fi = Gt,
                mi = lt(mi = gn.now) && mi || function() {
                    return (new gn).getTime()
                },
                gi = 8 == Gn(b + "08") ? Gn : function(t, e) {
                    return Gn(Lt(t) ? t.replace(k, "") : t, e || 0)
                };
            return f.after = Ne, f.assign = oi, f.at = jt, f.bind = Ie, f.bindAll = Le, f.bindKey = Oe, f.chain = ln, f.compact = he, f.compose = Be, f.constant = Ke, f.countBy = ci, f.create = mt, f.createCallback = Ye, f.curry = Fe, f.debounce = $e, f.defaults = ai, f.defer = He, f.delay = ze, f.difference = de, f.filter = Gt, f.flatten = ge, f.forEach = Xt, f.forEachRight = Qt, f.forIn = si, f.forInRight = _t, f.forOwn = ui, f.forOwnRight = vt, f.functions = bt, f.groupBy = hi, f.indexBy = di, f.initial = _e, f.intersection = ve, f.invert = xt, f.invoke = Jt, f.keys = ti, f.map = Zt, f.mapValues = Bt, f.max = te, f.memoize = Ve, f.merge = Ft, f.min = ee, f.omit = $t, f.once = Ue, f.pairs = Ht, f.partial = je, f.partialRight = We, f.pick = zt, f.pluck = pi, f.property = en, f.pull = xe, f.range = we, f.reject = re, f.remove = Ce, f.rest = Ae, f.shuffle = ae, f.sortBy = le, f.tap = cn, f.throttle = qe, f.times = an, f.toArray = ce, f.transform = Vt, f.union = Ee, f.uniq = ke, f.values = Ut, f.where = fi, f.without = De, f.wrap = Ge, f.xor = Re, f.zip = Me, f.zipObject = Pe, f.collect = Zt, f.drop = Ae, f.each = Xt, f.eachRight = Qt, f.extend = oi, f.methods = bt, f.object = Pe, f.select = Gt, f.tail = Ae, f.unique = ke, f.unzip = Me, Je(f), f.clone = pt, f.cloneDeep = ft, f.contains = Wt, f.escape = Xe, f.every = qt, f.find = Kt, f.findIndex = pe, f.findKey = gt, f.findLast = Yt, f.findLastIndex = fe, f.findLastKey = yt, f.has = St, f.identity = Qe, f.indexOf = ye, f.isArguments = dt, f.isArray = Jn, f.isBoolean = wt, f.isDate = Ct, f.isElement = At, f.isEmpty = Tt, f.isEqual = Et, f.isFinite = kt, f.isFunction = Dt, f.isNaN = Mt, f.isNull = Pt, f.isNumber = Nt, f.isObject = Rt, f.isPlainObject = li, f.isRegExp = It, f.isString = Lt, f.isUndefined = Ot, f.lastIndexOf = Se, f.mixin = Je, f.noConflict = Ze, f.noop = tn, f.now = mi, f.parseInt = gi, f.random = nn, f.reduce = ne, f.reduceRight = ie, f.result = rn, f.runInContext = d, f.size = se, f.some = ue, f.sortedIndex = Te, f.template = on, f.unescape = sn, f.uniqueId = un, f.all = qt, f.any = ue, f.detect = Kt, f.findWhere = Kt, f.foldl = ne, f.foldr = ie, f.include = Wt, f.inject = ne, Je(function() {
                var t = {};
                return ui(f, function(e, n) {
                    f.prototype[n] || (t[n] = e)
                }), t
            }(), !1), f.first = me, f.last = be, f.sample = oe, f.take = me, f.head = me, ui(f, function(t, e) {
                var n = "sample" !== e;
                f.prototype[e] || (f.prototype[e] = function(e, i) {
                    var r = this.__chain__,
                        o = t(this.__wrapped__, e, i);
                    return r || null != e && (!i || n && "function" == typeof e) ? new m(o, r) : o
                })
            }), f.VERSION = "2.4.1", f.prototype.chain = hn, f.prototype.toString = dn, f.prototype.value = pn, f.prototype.valueOf = pn, Xt(["join", "pop", "shift"], function(t) {
                var e = Cn[t];
                f.prototype[t] = function() {
                    var t = this.__chain__,
                        n = e.apply(this.__wrapped__, arguments);
                    return t ? new m(n, t) : n
                }
            }), Xt(["push", "reverse", "sort", "unshift"], function(t) {
                var e = Cn[t];
                f.prototype[t] = function() {
                    return e.apply(this.__wrapped__, arguments), this
                }
            }), Xt(["concat", "slice", "splice"], function(t) {
                var e = Cn[t];
                f.prototype[t] = function() {
                    return new m(e.apply(this.__wrapped__, arguments), this.__chain__)
                }
            }), f
        }
        var p, f = [],
            m = [],
            g = 0,
            y = +new Date + "",
            _ = 75,
            v = 40,
            b = " 	\f\xa0\ufeff\n\r\u2028\u2029\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u202f\u205f\u3000",
            S = /\b__p \+= '';/g,
            x = /\b(__p \+=) '' \+/g,
            w = /(__e\(.*?\)|\b__t\)) \+\n'';/g,
            C = /\$\{([^\\}]*(?:\\.[^\\}]*)*)\}/g,
            A = /\w*$/,
            T = /^\s*function[ \n\r\t]+\w/,
            E = /<%=([\s\S]+?)%>/g,
            k = RegExp("^[" + b + "]*0+(?=.$)"),
            D = /($^)/,
            R = /\bthis\b/,
            M = /['\n\r\t\u2028\u2029\\]/g,
            P = ["Array", "Boolean", "Date", "Function", "Math", "Number", "Object", "RegExp", "String", "_", "attachEvent", "clearTimeout", "isFinite", "isNaN", "parseInt", "setTimeout"],
            N = 0,
            I = "[object Arguments]",
            L = "[object Array]",
            O = "[object Boolean]",
            B = "[object Date]",
            F = "[object Function]",
            $ = "[object Number]",
            H = "[object Object]",
            z = "[object RegExp]",
            V = "[object String]",
            U = {};
        U[F] = !1, U[I] = U[L] = U[O] = U[B] = U[$] = U[H] = U[z] = U[V] = !0;
        var j = {
                leading: !1,
                maxWait: 0,
                trailing: !1
            },
            W = {
                configurable: !1,
                enumerable: !1,
                value: null,
                writable: !1
            },
            q = {
                "boolean": !1,
                "function": !0,
                object: !0,
                number: !1,
                string: !1,
                undefined: !1
            },
            G = {
                "\\": "\\",
                "'": "'",
                "\n": "n",
                "\r": "r",
                "	": "t",
                "\u2028": "u2028",
                "\u2029": "u2029"
            },
            K = q[typeof window] && window || this,
            Y = q[typeof exports] && exports && !exports.nodeType && exports,
            X = q[typeof module] && module && !module.nodeType && module,
            Q = X && X.exports === Y && Y,
            J = q[typeof global] && global;
        !J || J.global !== J && J.window !== J || (K = J);
        var Z = d();
        "function" == typeof define && "object" == typeof define.amd && define.amd ? (K._ = Z, define(function() {
            return Z
        })) : Y && X ? Q ? (X.exports = Z)._ = Z : Y._ = Z : K._ = Z
    }.call(this),
    /* jQuery UI - v1.10.3 - 2014-01-13
     * http://jqueryui.com
     * Includes: jquery.ui.core.js, jquery.ui.widget.js, jquery.ui.mouse.js
     * Copyright 2014 jQuery Foundation and other contributors; Licensed MIT */
     (function( $, undefined ) {

     var uuid = 0,
     	runiqueId = /^ui-id-\d+$/;

     $.ui = $.ui || {};

     $.extend( $.ui, {
     	version: "1.10.3",

     	keyCode: {
     		BACKSPACE: 8,
     		COMMA: 188,
     		DELETE: 46,
     		DOWN: 40,
     		END: 35,
     		ENTER: 13,
     		ESCAPE: 27,
     		HOME: 36,
     		LEFT: 37,
     		NUMPAD_ADD: 107,
     		NUMPAD_DECIMAL: 110,
     		NUMPAD_DIVIDE: 111,
     		NUMPAD_ENTER: 108,
     		NUMPAD_MULTIPLY: 106,
     		NUMPAD_SUBTRACT: 109,
     		PAGE_DOWN: 34,
     		PAGE_UP: 33,
     		PERIOD: 190,
     		RIGHT: 39,
     		SPACE: 32,
     		TAB: 9,
     		UP: 38
     	}
     });
     $.fn.extend({
     	focus: (function( orig ) {
     		return function( delay, fn ) {
     			return typeof delay === "number" ?
     				this.each(function() {
     					var elem = this;
     					setTimeout(function() {
     						$( elem ).focus();
     						if ( fn ) {
     							fn.call( elem );
     						}
     					}, delay );
     				}) :
     				orig.apply( this, arguments );
     		};
     	})( $.fn.focus ),

     	scrollParent: function() {
     		var scrollParent;
     		if (($.ui.ie && (/(static|relative)/).test(this.css("position"))) || (/absolute/).test(this.css("position"))) {
     			scrollParent = this.parents().filter(function() {
     				return (/(relative|absolute|fixed)/).test($.css(this,"position")) && (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
     			}).eq(0);
     		} else {
     			scrollParent = this.parents().filter(function() {
     				return (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
     			}).eq(0);
     		}

     		return (/fixed/).test(this.css("position")) || !scrollParent.length ? $(document) : scrollParent;
     	},

     	zIndex: function( zIndex ) {
     		if ( zIndex !== undefined ) {
     			return this.css( "zIndex", zIndex );
     		}

     		if ( this.length ) {
     			var elem = $( this[ 0 ] ), position, value;
     			while ( elem.length && elem[ 0 ] !== document ) {
     				position = elem.css( "position" );
     				if ( position === "absolute" || position === "relative" || position === "fixed" ) {
     					value = parseInt( elem.css( "zIndex" ), 10 );
     					if ( !isNaN( value ) && value !== 0 ) {
     						return value;
     					}
     				}
     				elem = elem.parent();
     			}
     		}

     		return 0;
     	},

     	uniqueId: function() {
     		return this.each(function() {
     			if ( !this.id ) {
     				this.id = "ui-id-" + (++uuid);
     			}
     		});
     	},

     	removeUniqueId: function() {
     		return this.each(function() {
     			if ( runiqueId.test( this.id ) ) {
     				$( this ).removeAttr( "id" );
     			}
     		});
     	}
     });

     // selectors
     function focusable( element, isTabIndexNotNaN ) {
     	var map, mapName, img,
     		nodeName = element.nodeName.toLowerCase();
     	if ( "area" === nodeName ) {
     		map = element.parentNode;
     		mapName = map.name;
     		if ( !element.href || !mapName || map.nodeName.toLowerCase() !== "map" ) {
     			return false;
     		}
     		img = $( "img[usemap=#" + mapName + "]" )[0];
     		return !!img && visible( img );
     	}
     	return ( /input|select|textarea|button|object/.test( nodeName ) ?
     		!element.disabled :
     		"a" === nodeName ?
     			element.href || isTabIndexNotNaN :
     			isTabIndexNotNaN) &&
     		visible( element );
     }

     function visible( element ) {
     	return $.expr.filters.visible( element ) &&
     		!$( element ).parents().addBack().filter(function() {
     			return $.css( this, "visibility" ) === "hidden";
     		}).length;
     }

     $.extend( $.expr[ ":" ], {
     	data: $.expr.createPseudo ?
     		$.expr.createPseudo(function( dataName ) {
     			return function( elem ) {
     				return !!$.data( elem, dataName );
     			};
     		}) :
     		function( elem, i, match ) {
     			return !!$.data( elem, match[ 3 ] );
     		},

     	focusable: function( element ) {
     		return focusable( element, !isNaN( $.attr( element, "tabindex" ) ) );
     	},

     	tabbable: function( element ) {
     		var tabIndex = $.attr( element, "tabindex" ),
     			isTabIndexNaN = isNaN( tabIndex );
     		return ( isTabIndexNaN || tabIndex >= 0 ) && focusable( element, !isTabIndexNaN );
     	}
     });

     if ( !$( "<a>" ).outerWidth( 1 ).jquery ) {
     	$.each( [ "Width", "Height" ], function( i, name ) {
     		var side = name === "Width" ? [ "Left", "Right" ] : [ "Top", "Bottom" ],
     			type = name.toLowerCase(),
     			orig = {
     				innerWidth: $.fn.innerWidth,
     				innerHeight: $.fn.innerHeight,
     				outerWidth: $.fn.outerWidth,
     				outerHeight: $.fn.outerHeight
     			};

     		function reduce( elem, size, border, margin ) {
     			$.each( side, function() {
     				size -= parseFloat( $.css( elem, "padding" + this ) ) || 0;
     				if ( border ) {
     					size -= parseFloat( $.css( elem, "border" + this + "Width" ) ) || 0;
     				}
     				if ( margin ) {
     					size -= parseFloat( $.css( elem, "margin" + this ) ) || 0;
     				}
     			});
     			return size;
     		}

     		$.fn[ "inner" + name ] = function( size ) {
     			if ( size === undefined ) {
     				return orig[ "inner" + name ].call( this );
     			}

     			return this.each(function() {
     				$( this ).css( type, reduce( this, size ) + "px" );
     			});
     		};

     		$.fn[ "outer" + name] = function( size, margin ) {
     			if ( typeof size !== "number" ) {
     				return orig[ "outer" + name ].call( this, size );
     			}

     			return this.each(function() {
     				$( this).css( type, reduce( this, size, true, margin ) + "px" );
     			});
     		};
     	});
     }

     if ( !$.fn.addBack ) {
     	$.fn.addBack = function( selector ) {
     		return this.add( selector == null ?
     			this.prevObject : this.prevObject.filter( selector )
     		);
     	};
     }

     if ( $( "<a>" ).data( "a-b", "a" ).removeData( "a-b" ).data( "a-b" ) ) {
     	$.fn.removeData = (function( removeData ) {
     		return function( key ) {
     			if ( arguments.length ) {
     				return removeData.call( this, $.camelCase( key ) );
     			} else {
     				return removeData.call( this );
     			}
     		};
     	})( $.fn.removeData );
     }

     // deprecated
     $.ui.ie = !!/msie [\w.]+/.exec( navigator.userAgent.toLowerCase() );

     $.support.selectstart = "onselectstart" in document.createElement( "div" );
     $.fn.extend({
     	disableSelection: function() {
     		return this.bind( ( $.support.selectstart ? "selectstart" : "mousedown" ) +
     			".ui-disableSelection", function( event ) {
     				event.preventDefault();
     			});
     	},

     	enableSelection: function() {
     		return this.unbind( ".ui-disableSelection" );
     	}
     });

     $.extend( $.ui, {
     	plugin: {
     		add: function( module, option, set ) {
     			var i,
     				proto = $.ui[ module ].prototype;
     			for ( i in set ) {
     				proto.plugins[ i ] = proto.plugins[ i ] || [];
     				proto.plugins[ i ].push( [ option, set[ i ] ] );
     			}
     		},
     		call: function( instance, name, args ) {
     			var i,
     				set = instance.plugins[ name ];
     			if ( !set || !instance.element[ 0 ].parentNode || instance.element[ 0 ].parentNode.nodeType === 11 ) {
     				return;
     			}

     			for ( i = 0; i < set.length; i++ ) {
     				if ( instance.options[ set[ i ][ 0 ] ] ) {
     					set[ i ][ 1 ].apply( instance.element, args );
     				}
     			}
     		}
     	},

     	hasScroll: function( el, a ) {

     		if ( $( el ).css( "overflow" ) === "hidden") {
     			return false;
     		}

     		var scroll = ( a && a === "left" ) ? "scrollLeft" : "scrollTop",
     			has = false;

     		if ( el[ scroll ] > 0 ) {
     			return true;
     		}

     		el[ scroll ] = 1;
     		has = ( el[ scroll ] > 0 );
     		el[ scroll ] = 0;
     		return has;
     	}
     });

     })( jQuery ),
     /*
      * jQuery UI Widget 1.10.3
      * http://jqueryui.com
      *
      * Copyright 2013 jQuery Foundation and other contributors
      * Released under the MIT license.
      * http://jquery.org/license
      *
      * http://api.jqueryui.com/jQuery.widget/
      */
     (function( $, undefined ) {

     var uuid = 0,
     	slice = Array.prototype.slice,
     	_cleanData = $.cleanData;
     $.cleanData = function( elems ) {
     	for ( var i = 0, elem; (elem = elems[i]) != null; i++ ) {
     		try {
     			$( elem ).triggerHandler( "remove" );
     		} catch( e ) {}
     	}
     	_cleanData( elems );
     };

     $.widget = function( name, base, prototype ) {
     	var fullName, existingConstructor, constructor, basePrototype,
     		// proxiedPrototype allows the provided prototype to remain unmodified
     		// so that it can be used as a mixin for multiple widgets (#8876)
     		proxiedPrototype = {},
     		namespace = name.split( "." )[ 0 ];

     	name = name.split( "." )[ 1 ];
     	fullName = namespace + "-" + name;

     	if ( !prototype ) {
     		prototype = base;
     		base = $.Widget;
     	}

     	// create selector for plugin
     	$.expr[ ":" ][ fullName.toLowerCase() ] = function( elem ) {
     		return !!$.data( elem, fullName );
     	};

     	$[ namespace ] = $[ namespace ] || {};
     	existingConstructor = $[ namespace ][ name ];
     	constructor = $[ namespace ][ name ] = function( options, element ) {
     		// allow instantiation without "new" keyword
     		if ( !this._createWidget ) {
     			return new constructor( options, element );
     		}

     		// allow instantiation without initializing for simple inheritance
     		// must use "new" keyword (the code above always passes args)
     		if ( arguments.length ) {
     			this._createWidget( options, element );
     		}
     	};
     	// extend with the existing constructor to carry over any static properties
     	$.extend( constructor, existingConstructor, {
     		version: prototype.version,
     		// copy the object used to create the prototype in case we need to
     		// redefine the widget later
     		_proto: $.extend( {}, prototype ),
     		// track widgets that inherit from this widget in case this widget is
     		// redefined after a widget inherits from it
     		_childConstructors: []
     	});

     	basePrototype = new base();
     	// we need to make the options hash a property directly on the new instance
     	// otherwise we'll modify the options hash on the prototype that we're
     	// inheriting from
     	basePrototype.options = $.widget.extend( {}, basePrototype.options );
     	$.each( prototype, function( prop, value ) {
     		if ( !$.isFunction( value ) ) {
     			proxiedPrototype[ prop ] = value;
     			return;
     		}
     		proxiedPrototype[ prop ] = (function() {
     			var _super = function() {
     					return base.prototype[ prop ].apply( this, arguments );
     				},
     				_superApply = function( args ) {
     					return base.prototype[ prop ].apply( this, args );
     				};
     			return function() {
     				var __super = this._super,
     					__superApply = this._superApply,
     					returnValue;

     				this._super = _super;
     				this._superApply = _superApply;

     				returnValue = value.apply( this, arguments );

     				this._super = __super;
     				this._superApply = __superApply;

     				return returnValue;
     			};
     		})();
     	});
     	constructor.prototype = $.widget.extend( basePrototype, {
     		// TODO: remove support for widgetEventPrefix
     		// always use the name + a colon as the prefix, e.g., draggable:start
     		// don't prefix for widgets that aren't DOM-based
     		widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix : name
     	}, proxiedPrototype, {
     		constructor: constructor,
     		namespace: namespace,
     		widgetName: name,
     		widgetFullName: fullName
     	});

     	// If this widget is being redefined then we need to find all widgets that
     	// are inheriting from it and redefine all of them so that they inherit from
     	// the new version of this widget. We're essentially trying to replace one
     	// level in the prototype chain.
     	if ( existingConstructor ) {
     		$.each( existingConstructor._childConstructors, function( i, child ) {
     			var childPrototype = child.prototype;

     			// redefine the child widget using the same prototype that was
     			// originally used, but inherit from the new version of the base
     			$.widget( childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto );
     		});
     		// remove the list of existing child constructors from the old constructor
     		// so the old child constructors can be garbage collected
     		delete existingConstructor._childConstructors;
     	} else {
     		base._childConstructors.push( constructor );
     	}

     	$.widget.bridge( name, constructor );
     };

     $.widget.extend = function( target ) {
     	var input = slice.call( arguments, 1 ),
     		inputIndex = 0,
     		inputLength = input.length,
     		key,
     		value;
     	for ( ; inputIndex < inputLength; inputIndex++ ) {
     		for ( key in input[ inputIndex ] ) {
     			value = input[ inputIndex ][ key ];
     			if ( input[ inputIndex ].hasOwnProperty( key ) && value !== undefined ) {
     				// Clone objects
     				if ( $.isPlainObject( value ) ) {
     					target[ key ] = $.isPlainObject( target[ key ] ) ?
     						$.widget.extend( {}, target[ key ], value ) :
     						// Don't extend strings, arrays, etc. with objects
     						$.widget.extend( {}, value );
     				// Copy everything else by reference
     				} else {
     					target[ key ] = value;
     				}
     			}
     		}
     	}
     	return target;
     };

     $.widget.bridge = function( name, object ) {
     	var fullName = object.prototype.widgetFullName || name;
     	$.fn[ name ] = function( options ) {
     		var isMethodCall = typeof options === "string",
     			args = slice.call( arguments, 1 ),
     			returnValue = this;

     		// allow multiple hashes to be passed on init
     		options = !isMethodCall && args.length ?
     			$.widget.extend.apply( null, [ options ].concat(args) ) :
     			options;

     		if ( isMethodCall ) {
     			this.each(function() {
     				var methodValue,
     					instance = $.data( this, fullName );
     				if ( !instance ) {
     					return $.error( "cannot call methods on " + name + " prior to initialization; " +
     						"attempted to call method '" + options + "'" );
     				}
     				if ( !$.isFunction( instance[options] ) || options.charAt( 0 ) === "_" ) {
     					return $.error( "no such method '" + options + "' for " + name + " widget instance" );
     				}
     				methodValue = instance[ options ].apply( instance, args );
     				if ( methodValue !== instance && methodValue !== undefined ) {
     					returnValue = methodValue && methodValue.jquery ?
     						returnValue.pushStack( methodValue.get() ) :
     						methodValue;
     					return false;
     				}
     			});
     		} else {
     			this.each(function() {
     				var instance = $.data( this, fullName );
     				if ( instance ) {
     					instance.option( options || {} )._init();
     				} else {
     					$.data( this, fullName, new object( options, this ) );
     				}
     			});
     		}

     		return returnValue;
     	};
     };

     $.Widget = function( /* options, element */ ) {};
     $.Widget._childConstructors = [];

     $.Widget.prototype = {
     	widgetName: "widget",
     	widgetEventPrefix: "",
     	defaultElement: "<div>",
     	options: {
     		disabled: false,

     		// callbacks
     		create: null
     	},
     	_createWidget: function( options, element ) {
     		element = $( element || this.defaultElement || this )[ 0 ];
     		this.element = $( element );
     		this.uuid = uuid++;
     		this.eventNamespace = "." + this.widgetName + this.uuid;
     		this.options = $.widget.extend( {},
     			this.options,
     			this._getCreateOptions(),
     			options );

     		this.bindings = $();
     		this.hoverable = $();
     		this.focusable = $();

     		if ( element !== this ) {
     			$.data( element, this.widgetFullName, this );
     			this._on( true, this.element, {
     				remove: function( event ) {
     					if ( event.target === element ) {
     						this.destroy();
     					}
     				}
     			});
     			this.document = $( element.style ?
     				// element within the document
     				element.ownerDocument :
     				// element is window or document
     				element.document || element );
     			this.window = $( this.document[0].defaultView || this.document[0].parentWindow );
     		}

     		this._create();
     		this._trigger( "create", null, this._getCreateEventData() );
     		this._init();
     	},
     	_getCreateOptions: $.noop,
     	_getCreateEventData: $.noop,
     	_create: $.noop,
     	_init: $.noop,

     	destroy: function() {
     		this._destroy();
     		this.element
     			.unbind( this.eventNamespace )
     			.removeData( this.widgetName )
     			.removeData( this.widgetFullName )
     			.removeData( $.camelCase( this.widgetFullName ) );
     		this.widget()
     			.unbind( this.eventNamespace )
     			.removeAttr( "aria-disabled" )
     			.removeClass(
     				this.widgetFullName + "-disabled " +
     				"ui-state-disabled" );

     		// clean up events and states
     		this.bindings.unbind( this.eventNamespace );
     		this.hoverable.removeClass( "ui-state-hover" );
     		this.focusable.removeClass( "ui-state-focus" );
     	},
     	_destroy: $.noop,

     	widget: function() {
     		return this.element;
     	},

     	option: function( key, value ) {
     		var options = key,
     			parts,
     			curOption,
     			i;

     		if ( arguments.length === 0 ) {
     			// don't return a reference to the internal hash
     			return $.widget.extend( {}, this.options );
     		}

     		if ( typeof key === "string" ) {
     			options = {};
     			parts = key.split( "." );
     			key = parts.shift();
     			if ( parts.length ) {
     				curOption = options[ key ] = $.widget.extend( {}, this.options[ key ] );
     				for ( i = 0; i < parts.length - 1; i++ ) {
     					curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
     					curOption = curOption[ parts[ i ] ];
     				}
     				key = parts.pop();
     				if ( value === undefined ) {
     					return curOption[ key ] === undefined ? null : curOption[ key ];
     				}
     				curOption[ key ] = value;
     			} else {
     				if ( value === undefined ) {
     					return this.options[ key ] === undefined ? null : this.options[ key ];
     				}
     				options[ key ] = value;
     			}
     		}

     		this._setOptions( options );

     		return this;
     	},
     	_setOptions: function( options ) {
     		var key;

     		for ( key in options ) {
     			this._setOption( key, options[ key ] );
     		}

     		return this;
     	},
     	_setOption: function( key, value ) {
     		this.options[ key ] = value;

     		if ( key === "disabled" ) {
     			this.widget()
     				.toggleClass( this.widgetFullName + "-disabled ui-state-disabled", !!value )
     				.attr( "aria-disabled", value );
     			this.hoverable.removeClass( "ui-state-hover" );
     			this.focusable.removeClass( "ui-state-focus" );
     		}

     		return this;
     	},

     	enable: function() {
     		return this._setOption( "disabled", false );
     	},
     	disable: function() {
     		return this._setOption( "disabled", true );
     	},

     	_on: function( suppressDisabledCheck, element, handlers ) {
     		var delegateElement,
     			instance = this;

     		if ( typeof suppressDisabledCheck !== "boolean" ) {
     			handlers = element;
     			element = suppressDisabledCheck;
     			suppressDisabledCheck = false;
     		}
     		if ( !handlers ) {
     			handlers = element;
     			element = this.element;
     			delegateElement = this.widget();
     		} else {
     			element = delegateElement = $( element );
     			this.bindings = this.bindings.add( element );
     		}

     		$.each( handlers, function( event, handler ) {
     			function handlerProxy() {
     				if ( !suppressDisabledCheck &&
     						( instance.options.disabled === true ||
     							$( this ).hasClass( "ui-state-disabled" ) ) ) {
     					return;
     				}
     				return ( typeof handler === "string" ? instance[ handler ] : handler )
     					.apply( instance, arguments );
     			}

     			if ( typeof handler !== "string" ) {
     				handlerProxy.guid = handler.guid =
     					handler.guid || handlerProxy.guid || $.guid++;
     			}

     			var match = event.match( /^(\w+)\s*(.*)$/ ),
     				eventName = match[1] + instance.eventNamespace,
     				selector = match[2];
     			if ( selector ) {
     				delegateElement.delegate( selector, eventName, handlerProxy );
     			} else {
     				element.bind( eventName, handlerProxy );
     			}
     		});
     	},

     	_off: function( element, eventName ) {
     		eventName = (eventName || "").split( " " ).join( this.eventNamespace + " " ) + this.eventNamespace;
     		element.unbind( eventName ).undelegate( eventName );
     	},

     	_delay: function( handler, delay ) {
     		function handlerProxy() {
     			return ( typeof handler === "string" ? instance[ handler ] : handler )
     				.apply( instance, arguments );
     		}
     		var instance = this;
     		return setTimeout( handlerProxy, delay || 0 );
     	},

     	_hoverable: function( element ) {
     		this.hoverable = this.hoverable.add( element );
     		this._on( element, {
     			mouseenter: function( event ) {
     				$( event.currentTarget ).addClass( "ui-state-hover" );
     			},
     			mouseleave: function( event ) {
     				$( event.currentTarget ).removeClass( "ui-state-hover" );
     			}
     		});
     	},

     	_focusable: function( element ) {
     		this.focusable = this.focusable.add( element );
     		this._on( element, {
     			focusin: function( event ) {
     				$( event.currentTarget ).addClass( "ui-state-focus" );
     			},
     			focusout: function( event ) {
     				$( event.currentTarget ).removeClass( "ui-state-focus" );
     			}
     		});
     	},

     	_trigger: function( type, event, data ) {
     		var prop, orig,
     			callback = this.options[ type ];

     		data = data || {};
     		event = $.Event( event );
     		event.type = ( type === this.widgetEventPrefix ?
     			type :
     			this.widgetEventPrefix + type ).toLowerCase();
     		event.target = this.element[ 0 ];
     		orig = event.originalEvent;
     		if ( orig ) {
     			for ( prop in orig ) {
     				if ( !( prop in event ) ) {
     					event[ prop ] = orig[ prop ];
     				}
     			}
     		}

     		this.element.trigger( event, data );
     		return !( $.isFunction( callback ) &&
     			callback.apply( this.element[0], [ event ].concat( data ) ) === false ||
     			event.isDefaultPrevented() );
     	}
     };

     $.each( { show: "fadeIn", hide: "fadeOut" }, function( method, defaultEffect ) {
     	$.Widget.prototype[ "_" + method ] = function( element, options, callback ) {
     		if ( typeof options === "string" ) {
     			options = { effect: options };
     		}
     		var hasOptions,
     			effectName = !options ?
     				method :
     				options === true || typeof options === "number" ?
     					defaultEffect :
     					options.effect || defaultEffect;
     		options = options || {};
     		if ( typeof options === "number" ) {
     			options = { duration: options };
     		}
     		hasOptions = !$.isEmptyObject( options );
     		options.complete = callback;
     		if ( options.delay ) {
     			element.delay( options.delay );
     		}
     		if ( hasOptions && $.effects && $.effects.effect[ effectName ] ) {
     			element[ method ]( options );
     		} else if ( effectName !== method && element[ effectName ] ) {
     			element[ effectName ]( options.duration, options.easing, callback );
     		} else {
     			element.queue(function( next ) {
     				$( this )[ method ]();
     				if ( callback ) {
     					callback.call( element[ 0 ] );
     				}
     				next();
     			});
     		}
     	};
     });

     })( jQuery ),
     /*
      * jQuery UI Mouse 1.10.3
      * http://jqueryui.com
      *
      * Copyright 2013 jQuery Foundation and other contributors
      * Released under the MIT license.
      * http://jquery.org/license
      *
      * http://api.jqueryui.com/mouse/
      *
      * Depends:
      *	jquery.ui.widget.js
      */
     (function( $, undefined ) {

     var mouseHandled = false;
     $( document ).mouseup( function() {
     	mouseHandled = false;
     });

     $.widget("ui.mouse", {
     	version: "1.10.3",
     	options: {
     		cancel: "input,textarea,button,select,option",
     		distance: 1,
     		delay: 0
     	},
     	_mouseInit: function() {
     		var that = this;

     		this.element
     			.bind("mousedown."+this.widgetName, function(event) {
     				return that._mouseDown(event);
     			})
     			.bind("click."+this.widgetName, function(event) {
     				if (true === $.data(event.target, that.widgetName + ".preventClickEvent")) {
     					$.removeData(event.target, that.widgetName + ".preventClickEvent");
     					event.stopImmediatePropagation();
     					return false;
     				}
     			});

     		this.started = false;
     	},
     	_mouseDestroy: function() {
     		this.element.unbind("."+this.widgetName);
     		if ( this._mouseMoveDelegate ) {
     			$(document)
     				.unbind("mousemove."+this.widgetName, this._mouseMoveDelegate)
     				.unbind("mouseup."+this.widgetName, this._mouseUpDelegate);
     		}
     	},

     	_mouseDown: function(event) {
     		if( mouseHandled ) { return; }

     		(this._mouseStarted && this._mouseUp(event));

     		this._mouseDownEvent = event;

     		var that = this,
     			btnIsLeft = (event.which === 1),
     			elIsCancel = (typeof this.options.cancel === "string" && event.target.nodeName ? $(event.target).closest(this.options.cancel).length : false);
     		if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
     			return true;
     		}

     		this.mouseDelayMet = !this.options.delay;
     		if (!this.mouseDelayMet) {
     			this._mouseDelayTimer = setTimeout(function() {
     				that.mouseDelayMet = true;
     			}, this.options.delay);
     		}

     		if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
     			this._mouseStarted = (this._mouseStart(event) !== false);
     			if (!this._mouseStarted) {
     				event.preventDefault();
     				return true;
     			}
     		}

     		// Click event may never have fired (Gecko & Opera)
     		if (true === $.data(event.target, this.widgetName + ".preventClickEvent")) {
     			$.removeData(event.target, this.widgetName + ".preventClickEvent");
     		}

     		// these delegates are required to keep context
     		this._mouseMoveDelegate = function(event) {
     			return that._mouseMove(event);
     		};
     		this._mouseUpDelegate = function(event) {
     			return that._mouseUp(event);
     		};
     		$(document)
     			.bind("mousemove."+this.widgetName, this._mouseMoveDelegate)
     			.bind("mouseup."+this.widgetName, this._mouseUpDelegate);

     		event.preventDefault();

     		mouseHandled = true;
     		return true;
     	},

     	_mouseMove: function(event) {
     		if ($.ui.ie && ( !document.documentMode || document.documentMode < 9 ) && !event.button) {
     			return this._mouseUp(event);
     		}

     		if (this._mouseStarted) {
     			this._mouseDrag(event);
     			return event.preventDefault();
     		}

     		if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
     			this._mouseStarted =
     				(this._mouseStart(this._mouseDownEvent, event) !== false);
     			(this._mouseStarted ? this._mouseDrag(event) : this._mouseUp(event));
     		}

     		return !this._mouseStarted;
     	},

     	_mouseUp: function(event) {
     		$(document)
     			.unbind("mousemove."+this.widgetName, this._mouseMoveDelegate)
     			.unbind("mouseup."+this.widgetName, this._mouseUpDelegate);

     		if (this._mouseStarted) {
     			this._mouseStarted = false;

     			if (event.target === this._mouseDownEvent.target) {
     				$.data(event.target, this.widgetName + ".preventClickEvent", true);
     			}

     			this._mouseStop(event);
     		}

     		return false;
     	},

     	_mouseDistanceMet: function(event) {
     		return (Math.max(
     				Math.abs(this._mouseDownEvent.pageX - event.pageX),
     				Math.abs(this._mouseDownEvent.pageY - event.pageY)
     			) >= this.options.distance
     		);
     	},

     	_mouseDelayMet: function(/* event */) {
     		return this.mouseDelayMet;
     	},

     	_mouseStart: function(/* event */) {},
     	_mouseDrag: function(/* event */) {},
     	_mouseStop: function(/* event */) {},
     	_mouseCapture: function(/* event */) { return true; }
     });

     })(jQuery),
    /*
     * jQuery UI Touch Punch 0.2.3
     *
     * Copyright 2011–2014, Dave Furfero
     * Dual licensed under the MIT or GPL Version 2 licenses.
     *
     * Depends:
     *  jquery.ui.widget.js
     *  jquery.ui.mouse.js
     */
     (function ($) {

   // Detect touch support
   $.support.touch = 'ontouchend' in document;

   if (!$.support.touch) {
     return;
   }

   var mouseProto = $.ui.mouse.prototype,
       _mouseInit = mouseProto._mouseInit,
       _mouseDestroy = mouseProto._mouseDestroy,
       touchHandled;

   function simulateMouseEvent (event, simulatedType) {

     if (event.originalEvent.touches.length > 1) {
       return;
     }

     event.preventDefault();

     var touch = event.originalEvent.changedTouches[0],
         simulatedEvent = document.createEvent('MouseEvents');

     simulatedEvent.initMouseEvent(
       simulatedType,    // type
       true,             // bubbles
       true,             // cancelable
       window,           // view
       1,                // detail
       touch.screenX,    // screenX
       touch.screenY,    // screenY
       touch.clientX,    // clientX
       touch.clientY,    // clientY
       false,            // ctrlKey
       false,            // altKey
       false,            // shiftKey
       false,            // metaKey
       0,                // button
       null              // relatedTarget
     );
     event.target.dispatchEvent(simulatedEvent);
   }

   mouseProto._touchStart = function (event) {

     var self = this;
     if (touchHandled || !self._mouseCapture(event.originalEvent.changedTouches[0])) {
       return;
     }
     touchHandled = true;
     self._touchMoved = false;
     simulateMouseEvent(event, 'mouseover');
     simulateMouseEvent(event, 'mousemove');
     simulateMouseEvent(event, 'mousedown');
   };

   mouseProto._touchMove = function (event) {

     if (!touchHandled) {
       return;
     }
     this._touchMoved = true;
     simulateMouseEvent(event, 'mousemove');
   };

   mouseProto._touchEnd = function (event) {

     if (!touchHandled) {
       return;
     }

     simulateMouseEvent(event, 'mouseup');
     simulateMouseEvent(event, 'mouseout');
     if (!this._touchMoved) {
       simulateMouseEvent(event, 'click');
     }
     touchHandled = false;
   };

   mouseProto._mouseInit = function () {

     var self = this;
     self.element.bind({
       touchstart: $.proxy(self, '_touchStart'),
       touchmove: $.proxy(self, '_touchMove'),
       touchend: $.proxy(self, '_touchEnd')
     });
     _mouseInit.call(self);
   };

   mouseProto._mouseDestroy = function () {

     var self = this;
     self.element.unbind({
       touchstart: $.proxy(self, '_touchStart'),
       touchmove: $.proxy(self, '_touchMove'),
       touchend: $.proxy(self, '_touchEnd')
     });

     _mouseDestroy.call(self);
   };

 })(jQuery),
    function(t) {
        t.fn.prepareTransition = function() {
            return this.each(function() {
                var e = t(this);
                e.one("TransitionEnd webkitTransitionEnd transitionend oTransitionEnd", function() {
                    e.removeClass("is-transitioning")
                });
                var n = ["transition-duration", "-moz-transition-duration", "-webkit-transition-duration", "-o-transition-duration"],
                    i = 0;
                t.each(n, function(t, n) {
                    i || (i = parseFloat(e.css(n)))
                }), 0 != i && (e.addClass("is-transitioning"), e[0].offsetWidth)
            })
        }
    }(jQuery);
(function() {}).call(this),
    function() {
        var t, e, n, i, r, o = [].slice;
        r = $("meta[name='sheel-admin-context']").attr("content"), null != r ? window.Sheel || (window.Sheel = JSON.parse(r)) : window.Sheel || (window.Sheel = {}), Sheel.config = {
            env: "production"
        },
        Sheel.routes = {
            root: "/admin",
            dashboard: "/admin",
            global_search: "lookup/",
            auth_login: "/admin/signin/",
        }, i = function() {
            var t, e, n, i, r, a, s, u, l;
            for (n = arguments[0], e = 2 <= arguments.length ? o.call(arguments, 1) : [], _.isObject(_.last(e)) && (s = e.pop()), u = n, i = r = 0, a = e.length; a > r; i = ++r) t = e[i], u = u.replace(":" + (i + 1), t);
            if (-1 !== u.indexOf(":")) throw "Path '" + u + "' is expecting more arguments";
            return l = s ? "?" + $.param(s) : "", {
                html: "" + u + l,
                json: u + ".json" + l
            }
        }, e = Sheel.routes;
        for (n in e) t = e[n], Sheel.routes[n] = i.bind(null, t);

    }.call(this),
    function(t, e) {
        "function" == typeof define && define.amd ? define(e) : "object" == typeof exports ? module.exports = e() : t.NProgress = e()
    }(this, function() {
        function t(t, e, n) {
            return e > t ? e : t > n ? n : t
        }
        function e(t) {
            return 100 * (-1 + t)
        }
        function n(t, n, i) {
            var r;
            return r = "translate3d" === l.positionUsing ? {
                transform: "translate3d(" + e(t) + "%,0,0)"
            } : "translate" === l.positionUsing ? {
                transform: "translate(" + e(t) + "%,0)"
            } : {
                "margin-left": e(t) + "%"
            }, r.transition = "all " + n + "ms " + i, r
        }

        function i(t, e) {
            var n = "string" == typeof t ? t : a(t);
            return n.indexOf(" " + e + " ") >= 0
        }

        function r(t, e) {
            var n = a(t),
                r = n + e;
            i(n, e) || (t.className = r.substring(1))
        }

        function o(t, e) {
            var n, r = a(t);
            i(t, e) && (n = r.replace(" " + e + " ", " "), t.className = n.substring(1, n.length - 1))
        }

        function a(t) {
            return (" " + (t.className || "") + " ").replace(/\s+/gi, " ")
        }

        function s(t) {
            t && t.parentNode && t.parentNode.removeChild(t)
        }
        var u = {};
        u.version = "0.1.6";
        var l = u.settings = {
            minimum: .08,
            easing: "ease",
            positionUsing: "",
            speed: 200,
            trickle: !0,
            trickleRate: .02,
            trickleSpeed: 800,
            showSpinner: !0,
            barSelector: '[role="bar"]',
            spinnerSelector: '[role="spinner"]',
            parent: "body",
            template: '<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
        };
        u.configure = function(t) {
                var e, n;
                for (e in t) n = t[e], void 0 !== n && t.hasOwnProperty(e) && (l[e] = n);
                return this
            }, u.status = null, u.set = function(e) {
                var i = u.isStarted();
                e = t(e, l.minimum, 1), u.status = 1 === e ? null : e;
                var r = u.render(!i),
                    o = r.querySelector(l.barSelector),
                    a = l.speed,
                    s = l.easing;
                return r.offsetWidth, c(function(t) {
                    "" === l.positionUsing && (l.positionUsing = u.getPositioningCSS()), h(o, n(e, a, s)), 1 === e ? (h(r, {
                        transition: "none",
                        opacity: 1
                    }), r.offsetWidth, setTimeout(function() {
                        h(r, {
                            transition: "all " + a + "ms linear",
                            opacity: 0
                        }), setTimeout(function() {
                            u.remove(), t()
                        }, a)
                    }, a)) : setTimeout(t, a)
                }), this
            }, u.isStarted = function() {
                return "number" == typeof u.status
            }, u.start = function() {
                u.status || u.set(0);
                var t = function() {
                    setTimeout(function() {
                        u.status && (u.trickle(), t())
                    }, l.trickleSpeed)
                };
                return l.trickle && t(), this
            }, u.done = function(t) {
                return t || u.status ? u.inc(.3 + .5 * Math.random()).set(1) : this
            }, u.inc = function(e) {
                var n = u.status;
                return n ? ("number" != typeof e && (e = (1 - n) * t(Math.random() * n, .1, .95)), n = t(n + e, 0, .994), u.set(n)) : u.start()
            }, u.trickle = function() {
                return u.inc(Math.random() * l.trickleRate)
            },
            function() {
                var t = 0,
                    e = 0;
                u.promise = function(n) {
                    return n && "resolved" != n.state() ? (0 == e && u.start(), t++, e++, n.always(function() {
                        e--, 0 == e ? (t = 0, u.done()) : u.set((t - e) / t)
                    }), this) : this
                }
            }(), u.render = function(t) {
                if (u.isRendered()) return document.getElementById("nprogress");
                r(document.documentElement, "nprogress-busy");
                var n = document.createElement("div");
                n.id = "nprogress", n.innerHTML = l.template;
                var i, o = n.querySelector(l.barSelector),
                    a = t ? "-100" : e(u.status || 0),
                    c = document.querySelector(l.parent);
                return h(o, {
                    transition: "all 0 linear",
                    transform: "translate3d(" + a + "%,0,0)"
                }), l.showSpinner || (i = n.querySelector(l.spinnerSelector), i && s(i)), c != document.body && r(c, "nprogress-custom-parent"), c.appendChild(n), n
            }, u.remove = function() {
                o(document.documentElement, "nprogress-busy"), o(document.querySelector(l.parent), "nprogress-custom-parent");
                var t = document.getElementById("nprogress");
                t && s(t)
            }, u.isRendered = function() {
                return !!document.getElementById("nprogress")
            }, u.getPositioningCSS = function() {
                var t = document.body.style,
                    e = "WebkitTransform" in t ? "Webkit" : "MozTransform" in t ? "Moz" : "msTransform" in t ? "ms" : "OTransform" in t ? "O" : "";
                return e + "Perspective" in t ? "translate3d" : e + "Transform" in t ? "translate" : "margin"
            };
        var c = function() {
                function t() {
                    var n = e.shift();
                    n && n(t)
                }
                var e = [];
                return function(n) {
                    e.push(n), 1 == e.length && t()
                }
            }(),
            h = function() {
                function t(t) {
                    return t.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function(t, e) {
                        return e.toUpperCase()
                    })
                }

                function e(t) {
                    var e = document.body.style;
                    if (t in e) return t;
                    for (var n, i = r.length, o = t.charAt(0).toUpperCase() + t.slice(1); i--;)
                        if (n = r[i] + o, n in e) return n;
                    return t
                }

                function n(n) {
                    return n = t(n), o[n] || (o[n] = e(n))
                }

                function i(t, e, i) {
                    e = n(e), t.style[e] = i
                }
                var r = ["Webkit", "O", "Moz", "ms"],
                    o = {};
                return function(t, e) {
                    var n, r, o = arguments;
                    if (2 == o.length)
                        for (n in e) r = e[n], void 0 !== r && e.hasOwnProperty(n) && i(t, n, r);
                    else i(t, o[1], o[2])
                }
            }();
        return u
    }),
    function() {
        var t, e, n, i, r, o, a, s, u, l, c, h, p, d, m, f, y, _, g, v, b, S, x, T, C, w, A, E, R, D = [].slice;
        for (window.Twine = {}, Twine.shouldDiscardEvent = {}, i = {}, f = 0, S = null, p = /^[a-z]\w*(\.[a-z]\w*|\[\d+\])*$/i, b = !1, x = null, n = null, Twine.reset = function(t, e) {
                var n, r, o, a, s, u;
                null == e && (e = document.documentElement);
                for (o in i)
                    if (n = null != (u = i[o]) ? u.bindings : void 0)
                        for (r = 0, a = n.length; a > r; r++) s = n[r], s.teardown && s.teardown();
                return i = {}, S = t, x = e, x.bindingId = f = 1, this
            }, Twine.bind = function(t, n) {
                return null == t && (t = x), null == n && (n = Twine.context(t)), e(n, t, !0)
            }, Twine.afterBound = function(t) {
                return n ? n.push(t) : t()
            }, e = function(t, r, o) {
                var a, u, l, c, p, d, m, y, _, g, v, b, x, C, w, A, E;
                n = [], r.bindingId && Twine.unbind(r), C = Twine.bindingTypes;
                for (E in C) a = C[E], (p = r.getAttribute(E) || r.getAttribute("data-" + E)) && (d || (d = {
                    bindings: []
                }), m = a(r, t, p, d), m && d.bindings.push(m));
                for ((x = r.getAttribute("context")) && (g = h(x), "$root" === g[0] && (t = S, g = g.slice(1)), t = s(t, g) || T(t, g, {})), (d || x || o) && ((null != d ? d : d = {}).childContext = t, i[null != r.bindingId ? r.bindingId : r.bindingId = ++f] = d), l = n, w = r.children || [], y = 0, v = w.length; v > y; y++) c = w[y], e(t, c);
                for (Twine.count = f, A = l || [], _ = 0, b = A.length; b > _; _++)(u = A[_])();
                return n = null, Twine
            }, Twine.refresh = function() {
                return b ? void 0 : (b = !0, setTimeout(Twine.refreshImmediately, 0))
            }, v = function(t) {
                var e, n, i, r;
                if (t.bindings)
                    for (r = t.bindings, e = 0, n = r.length; n > e; e++) i = r[e], null != i.refresh && i.refresh()
            }, Twine.refreshImmediately = function() {
                var t, e;
                b = !1;
                for (e in i) t = i[e], v(t)
            }, Twine.change = function(t, e) {
                var n;
                return null == e && (e = !1), n = document.createEvent("HTMLEvents"), n.initEvent("change", e, !0), t.dispatchEvent(n)
            }, Twine.unbind = function(t) {
                var e, n, r, o, a, s, u, l, c, h;
                if (r = t.bindingId) {
                    if (e = null != (c = i[r]) ? c.bindings : void 0)
                        for (o = 0, s = e.length; s > o; o++) l = e[o], l.teardown && l.teardown();
                    delete i[r], delete t.bindingId
                }
                for (h = t.children || [], a = 0, u = h.length; u > a; a++) n = h[a], Twine.unbind(n);
                return this
            }, Twine.context = function(t) {
                return a(t, !1)
            }, Twine.childContext = function(t) {
                return a(t, !0)
            }, a = function(t, e) {
                for (var n, r, o; t;) {
                    if (t === x) return S;
                    if (e || (t = t.parentNode), (r = t.bindingId) && (n = null != (o = i[r]) ? o.childContext : void 0)) return n;
                    e && (t = t.parentNode)
                }
            }, Twine.contextKey = function(t, e) {
                var n, r, o, a, s;
                for (a = [], n = function(t) {
                        var n, i;
                        for (n in t)
                            if (i = t[n], e === i) {
                                a.unshift(n);
                                break
                            }
                        return e = t
                    }; t && t !== x && (t = t.parentNode);)(o = t.bindingId) && (r = null != (s = i[o]) ? s.childContext : void 0) && n(r);
                return t === x && n(S), a.join(".")
            }, E = function(t) {
                var e, n;
                return e = t.nodeName.toLowerCase(), "input" === e || "textarea" === e || "select" === e ? "checkbox" === (n = t.getAttribute("type")) || "radio" === n ? "checked" : "value" : "textContent"
            }, h = function(t) {
                var e, n, i, r, o, a;
                for (i = [], o = t.split("."), n = 0, r = o.length; r > n; n++)
                    if (t = o[n], -1 !== (a = t.indexOf("[")))
                        for (i.push(t.substr(0, a)), t = t.substr(a); - 1 !== (e = t.indexOf("]"));) i.push(parseInt(t.substr(1, e), 10)), t = t.substr(e + 1);
                    else i.push(t);
                return i
            }, s = function(t, e) {
                var n, i, r;
                for (n = 0, r = e.length; r > n; n++) i = e[n], null != t && (t = t[i]);
                return t
            }, T = function(t, e, n) {
                var i, r, o, a, s, u;
                for (u = e, e = 2 <= u.length ? D.call(u, 0, i = u.length - 1) : (i = 0, []), a = u[i++], r = 0, s = e.length; s > r; r++) o = e[r], t = null != t[o] ? t[o] : t[o] = {};
                return t[a] = n
            }, A = function(t) {
                var e, n, i, r;
                for (i = t.attributes.length, n = 0, r = ""; i > n;) e = t.attributes.item(n), r += e.nodeName + "='" + e.textContent + "'", n += 1;
                return r
            }, R = function(t, e, n) {
                var i, r, o;
                if (u(t) && (o = h(t))) return "$root" === o[0] ? function(t, e) {
                    return s(e, o)
                } : function(t, e) {
                    return s(t, o)
                };
                try {
                    return new Function(e, "with($context) { return " + t + " }")
                } catch (r) {
                    throw i = r, "Twine error: Unable to create function on " + n.nodeName + " node with attributes " + A(n)
                }
            }, u = function(t) {
                return "true" !== t && "false" !== t && "null" !== t && "undefined" !== t && p.test(t)
            }, o = function(t) {
                var e;
                return e = document.createEvent("CustomEvent"), e.initCustomEvent("bindings:change", !0, !1, {}), t.dispatchEvent(e)
            }, Twine.bindingTypes = {
                bind: function(t, e, n) {
                    var i, r, a, l, c, p, d, m, f, y, _, g;
                    return g = E(t), _ = t[g], c = void 0, f = void 0, r = "radio" === t.getAttribute("type"), a = R(n, "$context,$root", t), d = function() {
                        var n;
                        return n = a.call(t, e, S), n !== c && (c = n, n !== t[g]) ? (t[g] = r ? n === t.value : n, o(t)) : void 0
                    }, u(n) ? (m = function() {
                        if (r) {
                            if (!t.checked) return;
                            return T(e, l, t.value)
                        }
                        return T(e, l, t[g])
                    }, l = h(n), y = "textContent" !== g && "hidden" !== t.type, "$root" === l[0] && (e = S, l = l.slice(1)), null == _ || !y && "" === _ || null != (p = s(e, l)) || m(), y && (i = function() {
                        return s(e, l) !== this[g] ? (m(), Twine.refreshImmediately()) : void 0
                    }, $(t).on("input keyup change", i), f = function() {
                        return $(t).off("input keyup change", i)
                    }), {
                        refresh: d,
                        teardown: f
                    }) : {
                        refresh: d
                    }
                },
                "bind-show": function(t, e, n) {
                    var i, r;
                    return i = R(n, "$context,$root", t), r = void 0, {
                        refresh: function() {
                            var n;
                            return n = !i.call(t, e, S), n !== r ? $(t).toggleClass("hide", r = n) : void 0
                        }
                    }
                },
                "bind-class": function(t, e, n) {
                    var i, r;
                    return i = R(n, "$context,$root", t), r = {}, {
                        refresh: function() {
                            var n, o, a;
                            o = i.call(t, e, S);
                            for (n in o) a = o[n], !r[n] != !a && $(t).toggleClass(n, !!a);
                            return r = o
                        }
                    }
                },
                "bind-attribute": function(t, e, n) {
                    var i, r;
                    return i = R(n, "$context,$root", t), r = {}, {
                        refresh: function() {
                            var n, o, a;
                            o = i.call(t, e, S);
                            for (n in o) a = o[n], r[n] !== a && $(t).attr(n, a || null);
                            return r = o
                        }
                    }
                },
                define: function(t, e, n) {
                    var i, r, o, a;
                    i = R(n, "$context,$root", t), o = i.call(t, e, S);
                    for (r in o) a = o[r], e[r] = a
                },
                eval: function(t, e, n) {
                    var i;
                    i = R(n, "$context,$root", t), i.call(t, e, S)
                }
            }, C = function(t, e) {
                var n;
                return n = "checked" === t || "disabled" === t || "readOnly" === t, Twine.bindingTypes["bind-" + e] = function(e, i, r) {
                    var a, s;
                    return a = R(r, "$context,$root", e), s = void 0, {
                        refresh: function() {
                            var r;
                            return r = a.call(e, i, S), n && (r = !!r), r !== s ? (e[t] = s = r, "checked" === t ? o(e) : void 0) : void 0
                        }
                    }
                }
            }, _ = ["placeholder", "checked", "disabled", "href", "title", "readOnly", "src"], l = 0, d = _.length; d > l; l++) t = _[l], C(t, t);
        for (C("innerHTML", "unsafe-html"), y = function(t) {
                return ("submit" === t.type || "a" === t.currentTarget.nodeName.toLowerCase()) && "1" !== t.currentTarget.getAttribute("allow-default")
            }, w = function(t) {
                return Twine.bindingTypes["bind-event-" + t] = function(e, n, i) {
                    var r;
                    return r = function(r, o) {
                        var a, s;
                        return s = "function" == typeof(a = Twine.shouldDiscardEvent)[t] ? a[t](r) : void 0, (s || y(r)) && r.preventDefault(), s ? void 0 : (R(i, "$context,$root,event,data", e).call(e, n, S, r, o), Twine.refreshImmediately())
                    }, $(e).on(t, r), {
                        teardown: function() {
                            return $(e).off(t, r)
                        }
                    }
                }
            }, g = ["click", "dblclick", "mouseenter", "mouseleave", "mouseover", "mouseout", "mousedown", "mouseup", "submit", "dragenter", "dragleave", "dragover", "drop", "drag", "change", "keypress", "keydown", "keyup", "input", "error", "done", "success", "fail", "blur", "focus", "load"], c = 0, m = g.length; m > c; c++) r = g[c], w(r)
    }.call(this);
var SheelDetect = function() {};
SheelDetect.prototype = {},
function() {
    var t = function(t, e) {
    };
    Sheel.AbstractPopoverAutocomplete = function() {
        function e(e, n) {
        }
        var n, i, r, o, a;
        return i = ".js-autocomplete-suggestions", a = "bottom", n = 5e3, o = 500, r = 5e3, e.prototype.defaultOptions = function() {

        }, e.prototype.defaultTemplates = function() {
        }, e.prototype.inputChanged = function() {
        }, e.prototype.resetSuggestions = function() {
        }, e.prototype.isApplied = function(t) {
        }, e.prototype.selectSuggestion = function(t) {
        }, e.prototype.selectHeader = function(t) {
        }, e.prototype.shouldShowPopover = function() {
        }, e.prototype.hasInput = function() {
        }, e.prototype.onFocus = function(t) {
        }, e.prototype.onScroll = function() {
        }, e.prototype.hasMoreResults = function() {
        }, e.prototype.togglePopover = function(t) {
        }, e.prototype.onMouseOut = function() {
        }, e.prototype.isPopoverClosing = function() {
        }, e.prototype.renderAndBindSuggestions = function(t) {
        }, e.prototype.selectDefaultSuggestion = function() {
        }, e.prototype.indexOfMatchedSuggestion = function() {
        }, e.prototype.renderSuggestionsList = function(t) {
        }, e.prototype.appendAndBindRenderedContent = function(t) {
        }, e.prototype.renderNoSuggestions = function() {
        }, e.prototype.showHeaderSuggestion = function() {
        }, e.prototype.canSelectHeaderSuggestion = function() {
        }, e.prototype.renderHeaderSuggestion = function() {
        }, e.prototype.headerSuggestionContent = function() {
        }, e.prototype.displayMoreResults = function() {
        }, e.prototype._renderAndAddMoreSuggestions = function(t) {
        }, e.prototype.bindSuggestions = function(t) {
        }, e.prototype.filterSourceArrayByInput = function() {
        }, e.prototype.makeLowerCaseSourceSet = function(t) {
        }, e.prototype.resourceFetchLimit = function() {
        }, e.prototype.fetchSuggestions = function(t, e, n) {
        }, e.prototype.showMaxLengthError = function() {
        }, e.prototype._showError = function(t) {
        }, e.prototype._clearError = function() {
        }, e.prototype._precompileTemplates = function(t) {
        }, e
    }()
}.call(this),
function() {
    window.Bindings = window.Twine
}.call(this),
function() {
    var t = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    };
    $(document).keyup(function(t) {
        return t.keyCode === Sheel.Keycodes.ESCAPE ? (Sheel.Modal.hide(), Sheel.Dropdown.hide()) : void 0
    }), Sheel.Modal = function() {
        function e(e, n) {
            var i, r;
            this.node = e, this.options = null != n ? n : {}, this.reverseFocusRestrict = t(this.reverseFocusRestrict, this), this.focusRestrict = t(this.focusRestrict, this), this.onAnimationEnd = t(this.onAnimationEnd, this), this.show = t(this.show, this), this.isFullscreen = !1, this.options.autoShow && setTimeout(this.show, 0), r = this.$modalButton(), r.length && (r.attr({
                role: "button",
                tabindex: 0
            }), r.on("keydown", this.launchModal), i = this.$container(), i.length && i.attr({
                role: "dialog",
                "aria-hidden": !0
            }))
        }
        var n, i, r, o, a, s, u, l, c;
        return s = "modal_container", o = "modal_backdrop", a = "modal-button", r = "animationend webkitAnimationEnd", u = "reverseFocusRestrict", c = !1, l = document.activeElement, n = null, i = null, e.currentModal = null, e.container = function() {
            return document.getElementById(s)
        }, e.backdrop = function() {
            return document.getElementById(o)
        }, e.button = function() {
            return document.getElementsByClassName(a)[0]
        }, e.prototype.$container = function() {
            return $(this.constructor.container())
        }, e.prototype.$backdrop = function() {
            return $(this.constructor.backdrop())
        }, e.prototype.$modalButton = function() {
            return $(this.constructor.button())
        }, e.hide = function(t) {
            var e;
            return null == t && (t = !1),
                null != (e = Sheel.Modal.currentModal) && e.hide(), t ? void 0 : Sheel.UIModal.hide(!0)
        }, e.onClickHide = function(t) {
            return t.preventDefault(), Sheel.Modal.hide()
        }, e.isActive = function(t) {
            var e;
            return null == t && (t = !1), e = null != Sheel.Modal.currentModal, t || e || (e = Sheel.UIModal.isVisible(!0)), e
        }, e.prototype.bindingsContext = function() {
            return Bindings.childContext(this.node)
        }, e.prototype.show = function(t) {
            var e, o, a, s, h, p;
            return null == t && (t = {}), t.context || (t.context = this.bindingsContext()), l = document.activeElement, c = !0, e = this.$backdrop(), a = this.$container(), a.length ? (t.keepOpen ? Sheel.UIModal.hide(!0) : Sheel.Modal.hide(), Sheel.Modal.currentModal = this, null == t.content && (h = this.node.href) ? void this.fetchHTML(h, t) : (this.options.noscroll && a.addClass("no-scroll"), this.options.move ? a.append(this.node.children) : (a.html(t.content || this.node.innerHTML), Bindings.bind(a[0], t.context).refreshImmediately()), a.one(r, this.onAnimationEnd), setTimeout(function() {
                return a.off(r, this.onAnimationEnd)
            }, 1e3), a.show(), t.keepOpen || a.addClass("modal-animate"), this.setSize(this.options.size), this.setHeight(this.options.height), a.on("click", ".close-modal", this.constructor.onClickHide), e.addClass("visible"), $("#wrapper").addClass("hide-when-printing"), "function" == typeof t.onRender && t.onRender(), s = a.find("h1, h2, h3, h4, h5, h6").first(), p = s.attr("id") || "ModalTitle", s.attr("id", p), a.attr({
                "aria-hidden": !1,
                "aria-labelledby": p,
                tabindex: -1
            }), a.focus(), a.on("keydown." + u, this.reverseFocusRestrict), e.attr("tabindex", 0), n = a.find(":focusable").first(), i = a.find(":focusable").last(), e.on("keydown", this.focusRestrict), o = a.find("[class=close-modal]"), o.attr({
                role: "button",
                "aria-label": "Close dialog"
            }), o.on("keydown", function(t) {
                var e;
                return (e = t.which) === Sheel.Keycodes.ENTER || e === Sheel.Keycodes.SPACE ? (t.preventDefault(), Sheel.Modal.hide()) : void 0
            }))) : void 0
        }, e.prototype.hide = function(t) {
            var e, n, i;
            return null == t && (t = {}), this.isShown() ? (e = this.$backdrop(), n = this.$container(), null != (i = this.request) && i.abort(), this.isFullscreen = !1, t.trigger !== !1 && n.trigger("modal:close"), this.options.noscroll && n.addClass("no-scroll"), this.options.move ? $(this.node).append(n.children()) : Bindings.unbind(n[0]), n.removeClass(function(t, e) {
                return (e.match(/\b\S+-modal/g) || []).join(" ")
            }), n.is(":visible") && (n.attr("aria-hidden", !0), c = !1, null != l && l.focus()), n.off("keydown." + u), n.hide().empty().off(), e.removeClass("visible").off(), e.attr("tabindex", -1), $("#wrapper").removeClass("hide-when-printing"), Sheel.Modal.currentModal = null, this.options.move ? $(this.node).trigger("modal:closed") : void 0) : void 0
        }, e.prototype.fetchHTML = function(t, e) {
            return null == e && (e = {}), Sheel.Loading.start(), this.request = $.ajax({
                dataType: "html",
                url: t,
                data: e.data
            }), this.request.done(function(t) {
                return function(n) {
                    var i;
                    return t.request.getResponseHeader("X-Sheel-Login-Required") ? (i = window.location.pathname.split("/admin/")[1], window.location.replace(Sheel.routes.auth_login({
                        redirect: i
                    }).html)) : t.show({
                        context: e.context,
                        content: "<div>" + n + "</div>",
                        onRender: e.onRender,
                        keepOpen: e.keepOpen || !1
                    })
                }
            }(this)), this.request.fail(Sheel.handleError), this.request.always(function(t) {
                return function() {
                    return t.request = null, Sheel.Loading.stop()
                }
            }(this))
        }, e.prototype.isShown = function() {
            return Sheel.Modal.currentModal === this
        }, e.prototype.setSize = function(t) {
            return this.isShown() && t && t.match(/^\b\S+$/) ? this.$container().addClass(t + "-modal") : void 0
        }, e.prototype.setHeight = function(t) {
            return null == t && (t = 300), this.isShown() && (t = Math.min(400, Math.max(100, t))) ? this.$container().find(".modal-iframe").css("height", t) : void 0
        }, e.prototype.toggleFullscreen = function() {
            return this.isShown() ? (this.$container().toggleClass("real-fullscreen-modal", !this.isFullscreen), this.isFullscreen = !this.isFullscreen) : void 0
        }, e.prototype.onClose = function(t) {
            return this.isShown() ? this.$container().one("modal:close", t) : void 0
        }, e.prototype.onAnimationEnd = function() {
            return this.$container().removeClass("modal-animate")
        }, e.prototype.launchModal = function(t) {
            var e;
            return c || (e = t.which) !== Sheel.Keycodes.ENTER && e !== Sheel.Keycodes.SPACE ? void 0 : (t.preventDefault(), t.target.click())
        }, e.prototype.focusRestrict = function(t) {
            return c && t.which === Sheel.Keycodes.TAB ? (t.stopPropagation(), this.$container().focus()) : void 0
        }, e.prototype.reverseFocusRestrict = function(t) {
            return t.which === Sheel.Keycodes.SHIFT ? t.stopPropagation() : c && t.shiftKey && n.is(":focus") ? (t.stopPropagation(), this.$backdrop().focus()) : void 0
        }, e
    }(), $(document).on("ready page:load", function() {
        return Sheel.Modal.container() && Sheel.Modal.currentModal && !Sheel.Modal.container().innerHTML.trim().length ? Sheel.Modal.hide() : void 0
    })
}.call(this),
function() {
    var t, e, n, i, r;
    e = location.href, n = {}, t = null, i = [], window.Page = function(e) {
        return t = e || function() {
            return {}
        }
    }, Page.getLastUrl = function() {
        return e
    }, Page.processMessage = function(t, e) {
        var n;
        return n = Bindings.context(document.querySelector("body")), n && "function" == typeof n.processMessage ? n.processMessage(t, e) : void 0
    }, Page.onRefresh = function(t, e, r) {
        var o;
        return r ? o = e : r = e, o ? Bindings.afterBound(function() {
            var e, a;
            return e = Bindings.contextKey(o, t), (a = n[e]) && i.push([a, r]), n[e] = t
        }) : void 0
    }, Page.pushState = function(t) {
        return window.history.pushState({
            turbolinks: !0,
            url: t
        }, null, t)
    }, Page.replaceState = function(t) {
        return window.history.replaceState({
            turbolinks: !0,
            url: t
        }, null, t)
    }, Page.openPopup = function(t, e, n) {
        var i, r;
        return i = {
            width: 500,
            height: 500,
            directories: "no",
            location: "no",
            menubar: "no",
            resizeable: "yes",
            scrollbars: "yes",
            toolbar: "no",
            status: "no"
        }, n = _.defaults(n, i), r = _.map(n, function(t, e) {
            return e + "=" + t
        }).toString(), Page.open(t, e, r)
    }, r = function(r) {
        var o, a, s, u, l, c, h, p, d;
        if (location.href !== e && (n = {}), null != t && (h = t() || {}, Bindings.reset(h), r || Bindings.bind(), t = null), r)
            for (a = 0, l = r.length; l > a; a++) p = r[a], Bindings.bind(p);
        for (u = 0, c = i.length; c > u; u++) d = i[u], s = d[0], o = d[1], o(s);
        i.length = 0, Bindings.refreshImmediately(), h && "function" == typeof h.pageLoaded && h.pageLoaded()
    }, document.addEventListener("DOMContentLoaded", function() {
        return r()
    }), document.addEventListener("page:load", function(t) {
        r(t.data), e = location.href
    }), document.addEventListener("page:before-partial-replace", function(t) {
        var e, n, i, r;
        for (r = t.data, e = 0, n = r.length; n > e; e++) i = r[e], Bindings.unbind(i)
    }), document.addEventListener("page:after-node-removed", function(t) {
        return $(t.data).remove()
    }), $(document).ajaxComplete(function() {
        return Bindings.refresh()
    })
}.call(this),
function() {
    var t = [].slice;
    Sheel.dedupedXHR = function(e, n) {
        var i, r;
        return null == n && (n = null), i = null, r = null,
            function() {
                var o, a, s;
                return a = 1 <= arguments.length ? t.call(arguments, 0) : [], "pending" === (null != i ? i.state() : void 0) && i.abort(), null != n ? (s = this, clearTimeout(r), o = function() {
                    return i = e.apply(s, a)
                }, r = setTimeout(o, n)) : i = e.apply(this, a)
            }
    }
}.call(this),
function() {
    var t, e, n, i, r, o, a, s, u, l, c, h, p, d, m, f = [].slice;
    t = {
        CHANGE: "element-queries:changed",
        RESIZE: "resize.element-queries"
    }, e = 5, u = {}, a = {}, n = function(t, e, n) {
        var i, r, o, s, u;
        for (u = a[t], r = 0, o = u.length; o > r; r++) i = u[r], s = _.extend(_.clone(n), {
            "class": e,
            active: i.node.classList.contains(e)
        }), i.operations.push(s)
    }, p = function(t) {
        var e, n, i, r, o;
        if (!a[t])
            for (e = a[t] = [], o = document.getElementsByClassName(t), n = 0, i = o.length; i > n; n++) r = o[n], e.push({
                node: r,
                operations: []
            })
    }, l = function() {
        return s(), _.defer(c)
    }, s = function() {
        var t, e, i, r;
        a = {};
        for (t in u)
            if (i = u[t], p(t), a[t].length)
                for (e in i) r = i[e], n(t, e, r)
    }, c = function() {
        var t, n, i, r, o, s, u, l, c, h, p, d, m, f, y, _, g, v, b, S, x, T, C, w;
        for (w = [], u = c = 1, x = e; x >= 1 ? x >= c : c >= x; u = x >= 1 ? ++c : --c) {
            i = [];
            for (r in a)
                for (s = a[r], h = 0, d = s.length; d > h; h++)
                    if (o = s[h], v = o.node, S = null != (T = v.parentNode) ? T.getBoundingClientRect().width : void 0)
                        for (C = o.operations, p = 0, m = C.length; m > p; p++) b = C[p], g = b.min, _ = b.max, t = b.active, l = null != g && null != _ ? S >= g && _ >= S : null != g ? S >= g : _ >= S, l && !t && (b.active = !0, i.push({
                            node: v,
                            operation: "add",
                            "class": b["class"]
                        })), !l && t && (b.active = !1, i.push({
                            node: v,
                            operation: "remove",
                            "class": b["class"]
                        }));
            for (y = 0, f = i.length; f > y; y++) n = i[y], n.node.classList[n.operation](n["class"]);
            if ((0 === i.length && u > 1 || u === e) && $(document).trigger(Sheel.ElementQueries.changedEvent), 0 === i.length) break;
            w.push(void 0)
        }
        return w
    }, o = _.debounce(c, 50), r = _.debounce(l, 50), m = function(t) {
        var e, n, i, o;
        for (n = !1, _.isArray(t) || (t = [t]), i = 0, o = t.length; o > i; i++)
            if (e = t[i], d(e.target)) {
                n = !0;
                break
            }
        return n ? r() : void 0
    }, d = function(t) {
        var e, n, i, r;
        if (null == t) return !1;
        if (r = Object.keys(u), !r.length) return !1;
        if (t.classList)
            for (n = 0, i = r.length; i > n; n++)
                if (e = r[n], t.classList.contains(e)) return !0;
        return null != ("function" == typeof t.querySelector ? t.querySelector("." + r.join(", .")) : void 0) ? !0 : void 0
    }, i = function() {
        return u = {}, a = {}
    }, $(function() {
        var e;
        return l(), $(window).on(t.RESIZE, o), e = new MutationObserver(m), e.observe(document, {
            subtree: !0,
            childList: !0
        })
    }), h = {
        addQuery: n,
        storeAllNodesFor: p,
        parseElements: s,
        runQueries: c,
        cleanup: i
    }, Sheel.ElementQueries = {
        changedEvent: t.CHANGE,
        add: function(t, e, i) {
            if (null == i.min && null == i.max) throw "You must provide at least a min or max value at which point to apply the '" + e + "' class to '" + t + "'";
            u[t] || (u[t] = {}), u[t][e] = i, p(t), n(t, e, i), c()
        },
        send: function() {
            var t, e, n;
            return e = arguments[0], t = 2 <= arguments.length ? f.call(arguments, 1) : [], t.length ? null != (n = h[e]) ? n.apply(null, t) : void 0 : h[e]
        }
    }
}.call(this),
function() {
    $(document).on("page:fetch turbograft:remote:init", function() {
        return Sheel.Loading.start()
    }), $(document).on("page:load turbograft:remote:always", function() {
        return Sheel.Loading._continueLoading ? Sheel.Loading._continue() : Sheel.Loading.stop()
    }), Sheel.Loading = {
        continueAfterRefresh: function(t) {
            var e;
            e = this._continueLoading, this._continueLoading = !0;
            try {
                return t()
            } finally {
                this._continueLoading = e
            }
        },
        start: function() {
            return this.loading ? void 0 : (this.loading = !0, NProgress.configure({
                parent: "body",
                barSelector: ".loading-bar",
                template: '<div class="loading-bar__container">\n  <div class="loading-bar"></div>\n</div>'
            }), NProgress.start(), $("body").addClass("is-loading"))
        },
        stop: function() {
            return this.loading = !1, NProgress.done(), $("body").removeClass("is-loading")
        },
        _continue: function() {
            return this.loading ? (NProgress.set(NProgress.status), $("body").addClass("is-loading")) : void 0
        }
    }
}.call(this),
function() {
    var t = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    };
    Sheel.Drawer = function() {
        function e(e) {
            this.drawerKeyup = t(this.drawerKeyup, this), this.$drawer = $(e), this.$parent = $("html, body"), this.$wrapper = $("#wrapper"), this.isOpen = this.$drawer.hasClass(this.DRAWER_OPEN_CLASS)
        }
        return e.prototype.NO_SCROLLING_CLASS = "helper--overflow-hidden", e.prototype.DRAWER_OPEN_CLASS = "nav-drawer--is-open", e.prototype.toggle = function() {
            return this.isOpen ? this.close() : this.open()
        }, e.prototype.open = function() {
            return this.isOpen ? void 0 : (this.$drawer.prepareTransition().addClass(this.DRAWER_OPEN_CLASS), this.$parent.addClass(this.NO_SCROLLING_CLASS), this.isOpen = !0, this.$wrapper.on("touchmove.drawer", function() {
                return !1
            }), $(document).on("keyup.drawer", this.drawerKeyup))
        }, e.prototype.close = function() {
            return this.isOpen ? (this.$drawer.prepareTransition().removeClass(this.DRAWER_OPEN_CLASS), this.$parent.removeClass(this.NO_SCROLLING_CLASS), this.isOpen = !1, this.$wrapper.off(".drawer"), $(document).off(".drawer")) : void 0
        }, e.prototype.drawerKeyup = function(t) {
            return t.keyCode === Sheel.Keycodes.ESCAPE ? this.close() : void 0
        }, e
    }()
}.call(this),
function() {
    var t = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    };
    Sheel.GlobalSearch = function() {
        function e(e, o, a) {
            null == o && (o = ""), this.navContext = a, this._nextNavContext = t(this._nextNavContext, this), this.goToResource = t(this.goToResource, this), this.prepareResults = t(this.prepareResults, this), this.searchError = t(this.searchError, this), this.searchSuccess = t(this.searchSuccess, this), this.onBodyKeydown = t(this.onBodyKeydown, this), this.onSearchInputKeydown = t(this.onSearchInputKeydown, this), this.onBodyClick = t(this.onBodyClick, this), this.debouncedSearch = Sheel.dedupedXHR(this.search, 150), this.debouncedAddToLastSearches = _.debounce(this._addToLastSearches, 1500), this.selectedIndex = -1, this.expanded = !1, this.lastSearch = !1, this.lastFocus = null, this.$lastElem = null, this.$results = null, this.navContext || (this.navContext = this._nextNavContext()), this.$node = $(e), this.$pane = this.$node.find("." + i), this.$input = this.$node.find("#" + p), this.$announce = this.$node.find("#" + n), this.$pane.on("click", "." + r.RESULT + " a", this.hideSearchPane.bind(this)), Page.onReplace(e, this.cleanupEventHandlers)
        }
        var n, i, r, o, a, s, u, l, c, h, p, d, m;
        return e.prototype.isLoading = !1, e.prototype.params = {
            limit: 10,
            q: ""
        }, c = "global-search-pane-results", s = "sheel_admin_searches", m = "webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", a = "GlobalSearch", u = "wrapper", p = "global-search-input", o = "model-filter-tab-bar", h = "global-search-results", n = "GlobalSearchPaneAnnounce", l = "helper--overflow-hidden", i = "global-search__pane", r = {
            BASE: i,
            RESULTS: "global-search__results",
            RESULT: "global-search__result",
            HEADING: "global-search__result__heading",
            TAB: "draw-tab",
            PAGINATION_LINK: "js-pagination-link"
        }, d = {
            BASE: {
                EXPANDED: "global-search__pane--is-expanded"
            },
            RESULT: {
                SELECTED: "global-search__result--selected"
            }
        }, e.prototype.onBodyClick = function(t) {
            return $(t.target).closest("#" + a).length ? void 0 : this.hideSearchPane()
        }, e.prototype.showSearchPane = function() {
            var t;
            if (!(this.expanded || Sheel.Modal.isActive() || Sheel.UIModal.isVisible())) return t = this, this.expanded = !0, this.lastFocus = document.activeElement, this.$lastElem = this.$pane.find("a, button").last(), Bindings.refresh(), this.$pane.addClass(d.BASE.EXPANDED), this.$pane.attr("aria-hidden", !1), this.$pane.one(m, function() {
                return t.$input.focus(), t.$input.val.length > 0 ? t.$input.select() : void 0
            }), this.navContext && this.navContext.searchSetup(), $(document).on("keydown.global-search", "body", this.onBodyKeydown.bind(this)), $(document).on("click.global-search", "body", this.onBodyClick.bind(this)), $(document).on("focus.search-result", "." + r.RESULT, function() {
                return $(this).addClass(d.RESULT.SELECTED)
            }), $(document).on("blur.search-result", "." + r.RESULT, function() {
                return $(this).removeClass(d.RESULT.SELECTED)
            }), $("#" + u).addClass(l)
        }, e.prototype.hideSearchPane = function() {
            return this.expanded ? (this.$input.blur(), this.expanded = !1, this.$pane.removeClass(d.BASE.EXPANDED), this.$pane.attr("aria-hidden", !0), this.navContext && this.navContext.searchTeardown(), this.lastFocus.focus(), Bindings.refresh(), $("#" + u).removeClass(l), this.cleanupEventHandlers()) : void 0
        }, e.prototype.cleanupEventHandlers = function() {
            return $(document).off(".global-search"), $(document).off(".search-result")
        }, e.prototype.toggleSearchPane = function() {
            return this.expanded ? this.hideSearchPane() : this.showSearchPane()
        }, e.prototype.onSearchInputKeydown = function(t) {
            switch (t.keyCode) {
                case jQuery.ui.keyCode.UP:
                case jQuery.ui.keyCode.DOWN:
                case jQuery.ui.keyCode.LEFT:
                case jQuery.ui.keyCode.RIGHT:
                case jQuery.ui.keyCode.TAB:
                    break;
                case jQuery.ui.keyCode.ESCAPE:
                    return this.hideSearchPane();
                case jQuery.ui.keyCode.ENTER:
                    return this.search(!0);
                default:
                    return this.searchQuery() && this.debouncedSearch(), this.$announce.html("")
            }
        }, e.prototype.onBodyKeydown = function(t) {
            var e;
            if (this.expanded) switch (t.keyCode) {
                case jQuery.ui.keyCode.ESCAPE:
                    return this.hideSearchPane();
                case jQuery.ui.keyCode.UP:
                case jQuery.ui.keyCode.DOWN:
                    if (!this.$input.val().length) return;
                    return e = t.keyCode === jQuery.ui.keyCode.UP ? -1 : 1, this.select(e), t.preventDefault(), t.stopPropagation();
                case jQuery.ui.keyCode.ENTER:
                    return $(t.target).is("." + r.PAGINATION_LINK) ? setTimeout(function(t) {
                        return function() {
                            return t.$results = t.$node.find("." + r.RESULT), t.prepareResults(), t.$results.first().focus()
                        }
                    }(this), 1e3) : this.goToResource(t);
                case jQuery.ui.keyCode.TAB:
                    if (this.$input.is(":focus") && t.shiftKey) return this.$announce.focus();
                    if (this.$lastElem.is(":focus") && !t.shiftKey) return this.$pane.focus()
            }
        }, e.prototype.searchQuery = function() {
            return this.$input.val()
        }, e.prototype.setSearch = function(t) {
            return this.$input.val(t).focus(), this.search(!0)
        }, e.prototype.clearSelection = function() {
            var t;
            return null != (t = this.$results) ? t.removeClass(d.RESULT.SELECTED) : void 0
        }, e.prototype.select = function(t) {
            var e, n, i;
            this.isLoading = !1, n = this.$results, i = n.length, this.selectedIndex += t, this.selectedIndex < 0 && (this.selectedIndex = i - 1), this.selectedIndex = this.selectedIndex % i, this.clearSelection(), this.selectedIndex >= 0 && (e = $(n[this.selectedIndex]), e.addClass(d.RESULT.SELECTED).focus(), this._scrollToResult(e))
        }, e.prototype.clearInput = function() {
            return this.$input.val("")
        }, e.prototype.search = function(t) {
            return null == t && (t = !1), this.searchQuery() !== this.lastSearch || t ? (this.lastSearch = this.searchQuery(), this.isLoading = !0, Bindings.refresh(), this.params.q = this.searchQuery(), this.debouncedAddToLastSearches(), $.ajax({
                type: "GET",
                url: Sheel.routes.global_search({
                    redirect: t
                }).html,
                data: this.params,
                dataType: "html"
            }).done(this.searchSuccess).fail(Sheel.handleError)) : void 0
        }, e.prototype.searchSuccess = function(t, e, n) {
            var i, o;
            return this.isLoading = !1, (i = n.getResponseHeader("X-Next-Redirect")) ? Page.visit(i, {
                reload: !0
            }) : (Bindings.refresh(), Page.refresh({
                response: n,
                onlyKeys: [c]
            }), this.$results = this.$node.find("." + r.RESULT), this.$lastElem = this.$pane.find("a, button").last(), this.$globalResults = $("#" + h), o = this.$globalResults.length ? this.$globalResults.data("total-results") : 0, this.announceResults(o), o ? this.prepareResults() : void 0)
        }, e.prototype.searchError = function(t, e, n) {
            return this.isLoading = !1, Sheel.Flash.error("An error occured while searching.")
        }, e.prototype.clearResults = function(t) {
            return null == t && (t = !1), this.selectedIndex = -1, this.clearInput(), this.clearSelection(), Bindings.refresh(), t && this.$input.focus(), this.search();
        }, e.prototype.announceResults = function(t) {
            return 0 === t ? this.$announce.html("No results.") : 1 === t ? this.$announce.html("1 result.") : this.$announce.html(t + " results.")
        }, e.prototype.prepareResults = function() {
            return this.$results.find("a, button").attr({
                tabindex: -1
            }), this.$results.each(function() {
                var t;
                return t = $(this), t.attr({
                    role: "link",
                    "aria-label": t.find("." + r.HEADING + " a").html(),
                    tabindex: 0
                })
            })
        }, e.prototype.goToResource = function(t) {
            var e;
            if (this.$results) return e = this.$results.filter("." + d.RESULT.SELECTED).find("a"), e.length ? (e[0].click(), setTimeout(function(t) {
                return function() {
                    return t.hideSearchPane()
                }
            }(this), 100)) : void 0
        }, e.prototype._nextNavContext = function() {
            return Bindings.context(document.body).iLNav
        }, e.prototype._addToLastSearches = function() {
            var t, e, n;
            return t = this.searchQuery(), "" !== t && (e = Sheel.Cookie.get(s), n = {}, e && (n = JSON.parse(decodeURIComponent(e))), t !== n[1]) ? (n[5] = n[4], n[4] = n[3], n[3] = n[2], n[2] = n[1], n[1] = t, Sheel.Cookie.set(s, encodeURIComponent(JSON.stringify(n)), 365)) : void 0
        }, e.prototype._scrollToResult = function(t) {
            var e;
            return e = this.$pane.find("." + r.RESULTS), Sheel.Scrolling.scrollToElement(e[0], t[0])
        }, e.prototype.setModel = function(t) {
            return "All" === t ? delete this.params.models : this.params.models = t, this.lastSearch = !1, this.search()
        }, e.baseClass = function() {
            return r.BASE
        }, e
    }()
}.call(this),
function() {
Sheel.Notifications = function() {
    function t(t) {
        null == t && (t = {}), this.compatible() && (this.requestedPermissionFor = {}, this.iconPath = t.iconPath, this.shopName = t.shopName, this.adStageTwo=!1, Sheel.MessageBus.registerHandler("order_placed", this.handleNewOrder.bind(this)))
    }
    return t.prototype.COOKIE_NAMESPACE = "dn", t.prototype.requestedPermission = function(t) {
        return this.compatible() ? this.requestedPermissionFor[t] : !1
    }, t.prototype.canBeEnabled = function() {
        return this.compatible()&&!this.isDenied()
    }, t.prototype.compatible = function() {
        return window.Notification
    }, t.prototype.hasNotificationPreferenceFor = function(t) {
        return this.compatible() ? null !== Sheel.Cookie.get(this.COOKIE_NAMESPACE + "-" + t) : void 0
    }, t.prototype.wantsNotificationFor = function(t) {
        return this.compatible() ? "1" === Sheel.Cookie.get(this.COOKIE_NAMESPACE + "-" + t) : void 0
    }, t.prototype.doesntWantNotificationFor = function(t) {
        return this.compatible() ? "0" === Sheel.Cookie.get(this.COOKIE_NAMESPACE + "-" + t) : void 0
    }, t.prototype.enableNotificationsFor = function(t) {
        return this.compatible() ? (Sheel.Cookie.set(this.COOKIE_NAMESPACE + "-" + t, "1", 365), Sheel.Flash.notice("Desktop notification settings updated")) : void 0
    }, t.prototype.disableNotificationsFor = function(t) {
        return this.compatible() ? (Sheel.Cookie.set(this.COOKIE_NAMESPACE + "-" + t, "0", 365), Sheel.Flash.notice("Desktop notification settings updated")) : void 0
    }, t.prototype.isDenied = function() {
        return this.compatible() ? "denied" === Notification.permission : !1
    }, t.prototype.hasPermission = function() {
        return this.compatible() ? "granted" === Notification.permission : !1
    }, t.prototype.shouldRequestPermission = function() {
        return this.compatible() ? "default" === Notification.permission : !1
    }, t.prototype.requestPermission = function(t) {
        return this.compatible() ? (Notification.requestPermission(function(e) {
            return function(n) {
                return e.setPermission(t, n)
            }
        }(this)), void(this.requestedPermissionFor[t]=!0)) : !1
    }, t.prototype.setPermission = function(t, e) {
        return this.compatible() ? ("granted" === e ? this.enableNotificationsFor(t) : this.disableNotificationsFor(t), delete this.requestedPermissionFor[t], Twine.refresh()) : void 0
    }, t.prototype.handleNewOrder = function(t) {
        var e;
        if (this.wantsNotificationFor("orders"))
            return this.notification = null != this.notification ? (this.notification.close(), this.currentOrders++, this.newNotification("You have " + this.currentOrders + " new orders!", "Click here to view your orders", Sheel.routes.orders().html)) : (this.currentOrders = 1, this.newNotification("New order!", "You have a new order totaling " + t.total_price + " " + t.currency, Sheel.routes.order(t.order_id).html)), this.notification.addEventListener("show", function() {
                return setTimeout(this.close.bind(this), 1e4)
            }), e = this, this.notification.addEventListener("close", function() {
                return e.notification === this ? e.notification = null : void 0
            })
    }, t.prototype.sendTestNotification = function() {
        return this.newNotification("You have 5 new orders!", "Click here to view your orders", Sheel.routes.orders().html)
    }, t.prototype.newNotification = function(t, e, n) {
        var i;
        return i = new Notification(t, {
            body: e,
            tag: "orderPlacedNotification",
            icon: this.iconPath
        }), i.addEventListener("click", function() {
            return window.open(n)
        }), i
    }, t.prototype.showOrderNotificationAdvertisement = function() {
        return this.canBeEnabled() && (!this.hasNotificationPreferenceFor("orders") || this.wantsNotificationsButNeedsPermission() || this.adStageTwo)
    }, t.prototype.wantsNotificationsButNeedsPermission = function() {
        return this.wantsNotificationFor("orders")&&!this.hasPermission()
    }, t.prototype.hasNoPreferenceNorRequestedPermission = function() {
        return !this.hasNotificationPreferenceFor("orders")&&!this.requestedPermission("orders")
    }, t.prototype.wantsButNullPermission = function() {
        return this.wantsNotificationFor("orders") && this.shouldRequestPermission()
    }, t.prototype.showFinalStage = function() {
        return this.hasPreferenceAndSomePermission() || this.doesntWantNotificationFor("orders")
    }, t.prototype.hasPreferenceAndSomePermission = function() {
        return this.hasNotificationPreferenceFor("orders")&&!this.shouldRequestPermission()
    }, t.prototype.requestedPermissionButHasNoPreference = function() {
        return this.requestedPermission("orders")&&!this.hasNotificationPreferenceFor("orders")
    }, t
}()
}.call(this),
function() {
Sheel.Cookie = function() {
    function t() {}
    return t.set = function(t, e, n) {
        var i, r;
        return n ? (i = new Date, i.setTime(i.getTime() + 24 * n * 60 * 60 * 1e3), r = "; expires=" + i.toGMTString()) : r = "", document.cookie = t + "=" + e + r + "; path=/"
    }, t.get = function(t) {
        var e, n, i, r, o, a, s;
        for (n = document.cookie.split(/;\s*/), a = 0, s = n.length; s > a; a++)
            if (e = n[a], o = e.indexOf("="), - 1 !== o && (i = e.substring(0, o), r = e.substring(o + 1), t === i))
                return r;
        return null
    }, t["delete"] = function(t) {
        return Sheel.Cookie.set(t, "", - 1)
    }, t
}()
}.call(this),
function() {
    Sheel.Keycodes = {
        ALT: 18,
        BACKSPACE: 8,
        COMMA: 188,
        COMMAND: 91,
        CONTROL: 17,
        DELETE: 46,
        DOWN: 40,
        END: 35,
        ENTER: 13,
        ESCAPE: 27,
        HOME: 36,
        LEFT: 37,
        NUMPAD_ADD: 107,
        NUMPAD_DECIMAL: 110,
        NUMPAD_DIVIDE: 111,
        NUMPAD_ENTER: 108,
        NUMPAD_MULTIPLY: 106,
        NUMPAD_SUBTRACT: 109,
        PAGE_DOWN: 34,
        PAGE_UP: 33,
        PERIOD: 190,
        RIGHT: 39,
        S: 83,
        SHIFT: 16,
        SPACE: 32,
        TAB: 9,
        UP: 38,
        Y: 89,
        Z: 90
    }
}.call(this),
function() {
    var t = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    };
    Sheel.Navi = function() {
        function e(e) {
            this.onResize = t(this.onResize, this), this.$nav = $(e), this.$primaryPanel = this.$nav.find("." + c.PANEL.PRIMARY), this.$secondaryPanel = this.$nav.find("." + c.PANEL.SECONDARY), this.$overlayPanel = this.$nav.find("." + c.PANEL.OVERLAY), this.$primaryLinks = this.$primaryPanel.find("." + r.LINK), this.$secondaryLinks = this.$secondaryPanel.find("." + r.LINK), this.$primaryList = this.$primaryPanel.find("." + c.LIST.PRIMARY), this.preventMouseEvents = !1, this.hovering = !1, this.breakpoint = window.matchMedia("screen and (max-width: 768px)"), this.$nav.hasClass(l.BASE.EXPANDED) ? this.currentState = s.EXPANDED : this.currentState = s.DEFAULT, $(window).on(u, _.debounce(this.onResize, 50)), Page.onReplace(e, function() {
                return $(window).off(u)
            })
        }
        var n, i, r, o, a, s, u, l, c, h;
        return i = "draw-nav", r = {
            BASE: i,
            PANEL: i + "__panel",
            LIST: i + "__list",
            LINK: i + "__link"
        }, c = {
            PANEL: {
                PRIMARY: r.PANEL + "--primary",
                SECONDARY: r.PANEL + "--secondary",
                OVERLAY: r.PANEL + "--icon-overlay"
            },
            LIST: {
                PRIMARY: r.LIST + "--primary",
                SECONDARY: r.LIST + "--secondary"
            }
        }, l = {
            BASE: {
                EXPANDED: r.BASE + "--is-expanded"
            },
            PANEL: {
                HIDDEN: r.PANEL + "--is-hidden",
                SCROLLBARS: r.PANEL + "--has-scrollbars"
            },
            LIST: {
                OPEN: r.LIST + "--is-open"
            },
            LINK: {
                SELECTED: r.LINK + "--is-selected",
                DISABLED: r.LINK + "--is-disabled"
            }
        }, n = {
            SECONDARY_NAV_ID: "data-secondary-nav-id",
            NAV_SECTION: "data-nav-section",
            NAV_SUB_ITEM: "data-nav-sub-item"
        }, s = {
            DEFAULT: "default",
            EXPANDED: "expanded"
        }, o = {
            KILL_TRANSITIONS: "helper--kill-transitions",
            TRANSITIONING: "is-transitioning"
        }, a = 500, u = "load.nav resize.nav", e.prototype.setPage = function(t) {
            var e, i;
            return this._clearAllTimeouts(), this.preventMouseEvents = !1, this.hovering = !1, this.primary = t[0], this.secondary = t[1], e = this.$primaryPanel.find("[" + n.NAV_SECTION + "='" + this.primary + "']"), e.hasClass(l.LINK.DISABLED) ? (e.removeClass(l.LINK.SELECTED), void this.setState(s.DEFAULT)) : (this.selectLink(e), this.secondary ? (i = this.$secondaryPanel.find("[" + n.NAV_SECTION + "='" + this.primary + "']"), i.length ? (this.toggleList(i), this.selectLink(i.find("[" + n.NAV_SUB_ITEM + "='" + this.secondary + "']")), this.setState(s.EXPANDED)) : void 0) : this.setState(s.DEFAULT))
        }, e.prototype.goTo = function(t, e) {
            var i, o, a, u;
            return null == e && (e = !1), t.metaKey || t.ctrlKey || t.altKey || t.shiftKey ? void 0 : (i = $(t.target).closest("." + r.LINK), i.hasClass(l.LINK.DISABLED) ? void t.preventDefault() : (e && (a = Twine.context(document.body).app) && a.host.setWindowLocation(t.target.href), this._clearAllTimeouts(), this.preventMouseEvents = !0, this.selectLink(i), i.parents("." + c.PANEL.PRIMARY).length ? (u = i.attr(n.SECONDARY_NAV_ID)) ? (o = $(u), this.toggleList(o), this.setState(s.EXPANDED), this.breakpoint.matches ? void t.preventDefault() : this.selectLink(o.find("." + r.LINK + ":not(." + l.LINK.DISABLED + ")").first())) : this.setState(s.DEFAULT) : void 0))
        }, e.prototype.setState = function(t) {
            var e, n;
            if (this.currentState !== t) return n = h(t), e = h(this.currentState), this.$nav.find("." + Sheel.UIPopover.CLASSES.BASE).length && Sheel.UIPopover["for"](this.$nav.find("." + Sheel.UIPopover.CLASSES.BASE)).deactivate(), t === s.DEFAULT ? this.$nav.prepareTransition().removeClass(e) : (this.$nav.removeClass(e), this.$nav.prepareTransition().addClass(n)), this.currentState = t
        }, h = function(t) {
            switch (t) {
                case s.EXPANDED:
                    return l.BASE.EXPANDED
            }
        }, e.prototype.selectLink = function(t) {
            return t.hasClass(l.LINK.SELECTED) ? void 0 : (t.parents("." + c.PANEL.PRIMARY).length && this.$primaryLinks.removeClass(l.LINK.SELECTED), this.$secondaryLinks.removeClass(l.LINK.SELECTED), _.defer(function(e) {
                return function() {
                    return t.addClass(l.LINK.SELECTED)
                }
            }(this)))
        }, e.prototype.toggleList = function(t) {
            var e;
            if (!t.hasClass(l.LIST.OPEN)) return e = this.$secondaryPanel.find("." + l.LIST.OPEN), e.removeClass(o.KILL_TRANSITIONS), this.currentState === s.EXPANDED ? e.prepareTransition().removeClass(l.LIST.OPEN) : e.removeClass(l.LIST.OPEN), _.defer(function(e) {
                return function() {
                    return e.$nav.hasClass(o.TRANSITIONING) ? t.addClass(l.LIST.OPEN).addClass(o.KILL_TRANSITIONS) : t.prepareTransition().addClass(l.LIST.OPEN)
                }
            }(this))
        }, e.prototype.hover = function() {
            return this.currentState === s.EXPANDED ? (this.setState(s.DEFAULT), this.hovering = !0) : void 0
        }, e.prototype.onMouseEnter = function(t) {
            return this.preventMouseEvents || Sheel.Loading.loading ? void 0 : $(t).hasClass(c.PANEL.PRIMARY) && this.currentState === s.EXPANDED ? void(this.primaryPanelMouseEnterTimeout = setTimeout(function(t) {
                return function() {
                    return t.hover()
                }
            }(this), a)) : $(t).hasClass(c.PANEL.SECONDARY) && this.currentState === s.EXPANDED ? (clearTimeout(this.primaryPanelMouseEnterTimeout), void(this.primaryPanelMouseEnterTimeout = null)) : void($(t).hasClass(r.BASE) && this.hovering && (clearTimeout(this.navMouseLeaveTimeout), this.navMouseLeaveTimeout = null))
        }, e.prototype.onMouseLeave = function(t) {
            this.preventMouseEvents || Sheel.Loading.loading || !this.hovering || $(t).hasClass(r.BASE) && this.currentState === s.DEFAULT && (this.navMouseLeaveTimeout = setTimeout(function(t) {
                return function() {
                    return t.setState(s.EXPANDED), t.hovering = !1
                }
            }(this), a))
        }, e.prototype.onResize = function() {
            return this.$primaryPanel.width() > this.$primaryList.width() ? this.$primaryPanel.addClass(l.PANEL.SCROLLBARS) : this.$primaryPanel.removeClass(l.PANEL.SCROLLBARS)
        }, e.prototype._clearAllTimeouts = function() {
            return this.primaryPanelMouseEnterTimeout && (clearTimeout(this.primaryPanelMouseEnterTimeout), this.primaryPanelMouseEnterTimeout = null), this.navMouseLeaveTimeout ? (clearTimeout(this.navMouseLeaveTimeout), this.navMouseLeaveTimeout = null) : void 0
        }, e.prototype.searchSetup = function() {
            return this._clearAllTimeouts(), this.preventMouseEvents = !0, this.$secondaryPanel.addClass(l.PANEL.HIDDEN), this.$overlayPanel.hide(), this.returnState = this.currentState, this.setState(s.EXPANDED)
        }, e.prototype.searchTeardown = function() {
            return this.preventMouseEvents = !1, this.$secondaryPanel.removeClass(l.PANEL.HIDDEN), this.$overlayPanel.show(), this.hovering || this.returnState === s.EXPANDED ? this.hovering = !1 : (this.$nav.removeClass(l.BASE.EXPANDED), this.currentState = s.DEFAULT)
        }, e
    }()
}.call(this),
function() {
    var t, e, n, i, r, o, a, s, u = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    };
    n = {
        BASE: "ui-modal",
        BODY: "ui-modal__body",
        CLOSE_BUTTON: "ui-modal__close-button"
    }, a = {
        BASE: {
            VISIBLE: "ui-modal--is-visible"
        },
        BODY: {
            SHADOW: "ui-modal__body--shadow",
            BOTTOM_SHADOW: "ui-modal__body--bottom-shadow",
            TOP_SHADOW: "ui-modal__body--top-shadow"
        },
        BACKDROP: {
            VISIBLE: "ui-modal-backdrop--is-visible"
        }
    }, i = "data-modal-context-ref-for", r = {
        MODAL_SHOW: "ui-modal:show",
        MODAL_HIDE: "ui-modal:hide"
    }, o = "ui-modal", e = "UIModalBackdrop", $(document).keyup(function(t) {
        return t.keyCode === Sheel.Keycodes.ESCAPE ? Sheel.UIModal.hide() : void 0
    }), s = t = void 0, $(function() {
        return t = $("#" + e), t.on("touchmove", function(t) {
            return !1
        })
    }), Sheel.UIModal = function() {
        function l(r, a) {
            var s;
            this.node = r, this.options = null != a ? a : {}, this.focusRestrict = u(this.focusRestrict, this), this.toggleBodyScrollShadow = u(this.toggleBodyScrollShadow, this), this.show = u(this.show, this), this.hide = u(this.hide, this), this.$contextReferenceNode = $(this.node), s = this.$contextReferenceNode.attr(i), this.$modal = $("#" + s), this.$body = this.$modal.find("." + n.BODY), t = $("#" + e), this.bound = !1, this.lastFocus = document.activeElement, this.lastScrollPosition = null, this.$firstElem = null, this.$lastElem = null, this.remote = this.$modal.data("remote"), this.remote && (this.href = this.$modal.attr("href")), Page.onReplace(this.$modal[0], function(t) {
                return function() {
                    return t.$modal.off("." + o), t.$body.off("." + o)
                }
            }(this)), this.$modal.data("start-visible") && this.show()
        }
        return l.hide = function(t) {
            return null == t && (t = !1), null != s && s.hide(), t ? void 0 : Sheel.Modal.hide(!0)
        }, l.isVisible = function(t) {
            var e;
            return null == t && (t = !1), e = null != s, t || e || (e = Sheel.Modal.isActive(!0)), e
        }, l.prototype.hide = function() {
            var e;
            return this.triggerModalHideEvent(), Sheel.ScrollLock.unlock(), t.removeClass(a.BACKDROP.VISIBLE), this.$modal.removeClass(a.BASE.VISIBLE).attr({
                "aria-hidden": !0
            }).off("." + o), this.$body.off("." + o), s = null, null != (e = this.lastFocus) ? e.focus() : void 0
        }, l.prototype.show = function(e) {
            return null == e && (e = {}), Sheel.UIModal.hide(), null == this.contentFetched && this.remote ? void this.fetchHTML(e) : (this.bound || this.bindContext(), this.triggerModalShowEvent(), this.lastFocus = document.activeElement, Sheel.ScrollLock.lock(), null != s && s.hide(), s = this, t.addClass(a.BACKDROP.VISIBLE), this.$modal.addClass(a.BASE.VISIBLE).on("click." + o, "." + n.CLOSE_BUTTON, this.hide).on("keydown." + o, this.focusRestrict).attr({
                "aria-hidden": !1,
                tabindex: -1
            }).focus(), this.$body.length && (this.$body.on("scroll." + o, _.debounce(this.toggleBodyScrollShadow, 50)), this.toggleBodyScrollShadow()), this.$firstElem = this.$modal.find(":focusable").first(), this.$lastElem = this.$modal.find(":focusable").last())
        }, l.prototype.fetchHTML = function(t) {
            return null == t && (t = {}), Sheel.Loading.start(), this.request = $.ajax({
                dataType: "html",
                url: this.href,
                data: t.data
            }), this.request.done(function(e) {
                return function(n) {
                    var i, r, o;
                    return e.request.getResponseHeader("X-Sheel-Login-Required") ? (r = window.location.pathname.split("/admin/")[1], window.location.replace(Sheel.routes.auth_login({
                        redirect: r
                    }).html)) : (i = null != (o = t.context) ? o : e.getContext(), e.appendContent(n, t.context, t.onRender))
                }
            }(this)), this.request.fail(Sheel.handleError), this.request.always(function(t) {
                return function() {
                    return t.request = null, Sheel.Loading.stop()
                }
            }(this))
        }, l.prototype.appendContent = function(t, e, i) {
            return this.contentFetched = !0, this.$modal.html(t), this.$body = this.$modal.find("." + n.BODY), Bindings.bind(this.$modal[0], e).refreshImmediately(), "function" == typeof i && i(), this.show()
        }, l.prototype.bindContext = function() {
            return Bindings.bind(this.$modal[0], this.getContext()).refreshImmediately(), this.bound = !0
        }, l.prototype.getContext = function() {
            return Bindings.context(this.$contextReferenceNode[0])
        }, l.prototype.triggerModalShowEvent = function() {
            return $(document).trigger(r.MODAL_SHOW)
        }, l.prototype.triggerModalHideEvent = function() {
            return $(document).trigger(r.MODAL_HIDE)
        }, l.prototype.toggleBodyScrollShadow = function() {
            var t, e, n;
            return e = !(this.$body.scrollTop() + this.$body.innerHeight() >= this.$body[0].scrollHeight), this.$body.toggleClass(a.BODY.BOTTOM_SHADOW, e), n = this.$body.scrollTop() > 0, this.$body.toggleClass(a.BODY.TOP_SHADOW, n), t = n && e, this.$body.toggleClass(a.BODY.SHADOW, t)
        }, l.prototype.focusRestrict = function(t) {
            if (this.$lastElem = this.$modal.find(":focusable").last(), this.isVisible() && t.which === Sheel.Keycodes.TAB) {
                if (t.stopPropagation(), !t.shiftKey && this.$lastElem[0] === document.activeElement) return this.$firstElem.focus(), t.preventDefault();
                if (t.shiftKey && this.$firstElem[0] === document.activeElement) return this.$lastElem.focus(), t.preventDefault()
            }
        }, l.prototype.isVisible = function() {
            return this.$modal.hasClass(a.BASE.VISIBLE)
        }, l
    }()
}.call(this),
function() {
    window.ComponentUrl = function() {
        function t(e) {
            return this.original = null != e ? e : document.location.href, this.original.constructor === t ? this.original : void this._parse()
        }
        return t.prototype.withoutHash = function() {
            return this.href.replace(this.hash, "")
        }, t.prototype.withoutHashForIE10compatibility = function() {
            return this.withoutHash()
        }, t.prototype.hasNoHash = function() {
            return 0 === this.hash.length
        }, t.prototype._parse = function() {
            var t;
            return (null != this.link ? this.link : this.link = document.createElement("a")).href = this.original, t = this.link, this.href = t.href, this.protocol = t.protocol, this.host = t.host, this.hostname = t.hostname, this.port = t.port, this.pathname = t.pathname, this.search = t.search, this.hash = t.hash, this.origin = [this.protocol, "//", this.hostname].join(""), 0 !== this.port.length && (this.origin += ":" + this.port), this.relative = [this.pathname, this.search, this.hash].join(""), this.absolute = this.href
        }, t
    }()
}.call(this),
function() {
    var t = function(t, n) {
        function i() {
            this.constructor = t
        }
        for (var r in n)
            e.call(n, r) && (t[r] = n[r]);
        return i.prototype = n.prototype, t.prototype = new i, t.__super__ = n.prototype, t
    }, e = {}.hasOwnProperty, n = [].slice;
    window.Link = function(e) {
        function i(t) {
            return this.link = t, this.link.constructor === i ? this.link : (this.original = this.link.href, void i.__super__.constructor.apply(this, arguments))
        }
        return t(i, e), i.HTML_EXTENSIONS = ["html"], i.allowExtensions = function() {
            var t, e, r, o;
            for (e = 1 <= arguments.length ? n.call(arguments, 0) : [], r = 0, o = e.length; o > r; r++)
                t = e[r], i.HTML_EXTENSIONS.push(t);
            return i.HTML_EXTENSIONS
        }, i.prototype.shouldIgnore = function() {
            return this._crossOrigin() || this._anchored() || this._nonHtml() || this._optOut() || this._target()
        }, i.prototype._crossOrigin = function() {
            return this.origin !== (new ComponentUrl).origin
        }, i.prototype._anchored = function() {
            var t;
            return (this.hash && this.withoutHash()) === (t = new ComponentUrl).withoutHash() || this.href === t.href + "#"
        }, i.prototype._nonHtml = function() {
            return this.pathname.match(/\.[a-z]+$/g)&&!this.pathname.match(new RegExp("\\.(?:" + i.HTML_EXTENSIONS.join("|") + ")?$", "g"))
        }, i.prototype._optOut = function() {
            var t, e;
            for (e = this.link; !t && e !== document && null !== e;)
                t = null != e.getAttribute("data-no-turbolink"), e = e.parentNode;
            return t
        }, i.prototype._target = function() {
            return 0 !== this.link.target.length
        }, i
    }(ComponentUrl)
}.call(this),
function() {
    var t, e, n, i, r, o, a, s, u, l, c, h, p, d, m, f, y, g, v, b, S, x, T, C, w, A, E, R, D, P, I, L, O, k, M, N, F, B, V, H, z, U = [].slice;
    e = "draw-popover", n = {
        BASE: e,
        CONTAINER: e + "__container",
        CONTENT_WRAPPER: e + "__content-wrapper",
        CONTENT: e + "__content",
        TOOLTIP: e + "__tooltip",
        PANE: e + "__pane"
    }, o = {
        BASE: {
            ACTIVE: n.BASE + "--is-active",
            POSITIONED_BELOW: n.BASE + "--is-positioned-beneath",
            POSITIONED_ABOVE: n.BASE + "--is-positioned-above"
        },
        CONTAINER: {
            ACTIVE: n.CONTAINER + "--contains-active-popover",
            DEACTIVATING: n.CONTAINER + "--is-deactivating"
        }
    }, u = {
        BASE: {
            ALIGN_TO_EDGE: n.BASE + "--align-to-edge",
            FULL_WIDTH: n.BASE + "--full-width",
            NO_FOCUS: n.BASE + "--do-not-show-on-focus",
            JS_ACTIVATED: n.BASE + "--js-activated"
        }
    }, i = {
        ACTIVATING: n.BASE + ":activating",
        ACTIVATED: n.BASE + ":activated",
        DEACTIVATED: n.BASE + ":deactivated",
        DEACTIVATING: n.BASE + ":deactivating"
    }, t = {
        PREFERRED_POSITION: "data-popover-preferred-position",
        HORIZONTALLY_RELATIVE_TO: "data-popover-horizontally-relative-to-closest",
        VERTICALLY_RELATIVE_TO: "data-popover-vertically-relative-to-closest",
        RELATIVE_TO: "data-popover-relative-to-closest",
        ACTIVATE_FROM: "data-popover-activate-from",
        CSS_CACHE: {
            MAX_HEIGHT: "data-popover-css-max-height",
            MAX_WIDTH: "data-popover-css-max-width",
            VERTICAL_MARGIN: "data-popover-css-vertical-margin",
            HORIZONTAL_MARGIN: "data-popover-css-horizontal-margin"
        }
    }, r = {
        TOP: "top",
        BOTTOM: "bottom"
    }, a = 5, l = 21, s = function(t) {
        var e, i;
        return e = $(t).siblings(":not(" + n.BASE + ")")[0], v(t), c(t, e), i = {
            activate: function() {
                h(t)
            },
            deactivate: function() {
                w(t)
            },
            toggle: function() {
                H(t)
            },
            reposition: function() {
                N()
            },
            isActive: function() {
                var e;
                return t === (null != (e = d.popover) ? e.base : void 0)
            }
        }, $(t).data(n.BASE, i), i
    }, s["for"] = function(t) {
        var e;
        return e = $(t).closest("." + n.BASE), e.length ? e.data(n.BASE) || s(e[0]) : void 0
    }, s.send = function() {
        var t, e, n;
        return e = arguments[0], t = 2 <= arguments.length ? U.call(arguments, 1) : [], t.length ? null != (n = B[e]) ? n.apply(null, t) : void 0 : B[e]
    }, s.deactivate = function() {
        return w()
    }, s.CLASSES = n, s.EVENTS = i, s.STATES = o, s.VARIANTS = u, s.ATTRS = t, s.POSITIONS = r, s.ZINDEX = l, Sheel.UIPopover = s, O = 1, c = function(t, e) {
        var i, r, o, a;
        return o = O++, a = t.id || n.BASE + "--" + o, r = I(e), i = r.id || n.BASE + "-activator--" + o, $(t).attr({
            id: a,
            "aria-labelledby": i,
            "aria-expanded": "false"
        }), $(r).attr({
            id: i,
            "aria-expanded": "false",
            "aria-haspopup": "true",
            "aria-owns": a,
            "aria-controls": a
        })
    }, v = function(e) {
        var i, r, o;
        return o = window.getComputedStyle(e), e.setAttribute(t.CSS_CACHE.VERTICAL_MARGIN, x(o.marginTop, e) || 0), e.setAttribute(t.CSS_CACHE.HORIZONTAL_MARGIN, x(o.marginLeft, e) || 0), i = $(e).children("." + n.CONTENT_WRAPPER)[0], r = window.getComputedStyle(i), e.setAttribute(t.CSS_CACHE.MAX_HEIGHT, x(r.maxHeight, i) || 1e4), e.setAttribute(t.CSS_CACHE.MAX_WIDTH, x(r.maxWidth, i) || 1e4), e.classList.contains(u.BASE.FULL_WIDTH) || (e.style.maxWidth = "none"), e.style.marginLeft = e.style.marginRight = "0px"
    }, g = function() {
        var t;
        return t = void 0,
            function() {
                var e, n;
                return null != t ? t : (e = $("<div>M</div>").appendTo("body"), e.css({
                    display: "inline-block",
                    padding: "0",
                    lineHeight: "1",
                    position: "absolute",
                    visibility: "hidden",
                    fontSize: "1em"
                }), n = e.height(), e.remove(), t)
            }
    }(), x = function(t, e) {
        var n;
        return "none" === t ? !1 : (n = parseFloat(t), t.indexOf("rem") >= 0 ? n * g() : t.indexOf("em") >= 0 ? n * parseFloat(window.getComputedStyle(e).fontSize) : t.indexOf("%") >= 0 ? n / 100 : n)
    }, d = {}, p = function(t) {
        var e, n, i, r;
        for (r = t.parentNode.children, n = 0, i = r.length; i > n; n++)
            if (e = r[n], e !== t && e.offsetWidth > 0) return e;
        return t.previousElementSibling || t.nextElementSibling
    }, I = function(t) {
        var e;
        return e = $(t), e.is(":input") ? t : e.find(":input")[0] || t
    }, h = function(e) {
        var o, a, s, l, c, h, f, _;
        if (e !== (null != (h = d.popover) ? h.base : void 0)) return d.popover && w(), f = e.querySelector("." + n.TOOLTIP), a = p(e), s = document.getElementById("wrapper") || document.body, c = $(e).closest(e.getAttribute(t.RELATIVE_TO)).get(0) || $(e).closest(e.getAttribute(t.HORIZONTALLY_RELATIVE_TO)).get(0) || s, _ = $(e).closest(e.getAttribute(t.RELATIVE_TO)).get(0) || $(e).closest(e.getAttribute(t.VERTICALLY_RELATIVE_TO)).get(0) || s, o = e.getAttribute(t.ACTIVATE_FROM), d = {
            container: e.parentNode,
            activator: a,
            activatorInput: I(a),
            source: o ? a.querySelector(o) : a,
            horizontallyRelativeTo: c,
            verticallyRelativeTo: _,
            preferredPosition: e.getAttribute(t.PREFERRED_POSITION) || r.BOTTOM,
            horizontallyPosition: !e.classList.contains(u.BASE.FULL_WIDTH),
            positionAgainstEdge: e.classList.contains(u.BASE.ALIGN_TO_EDGE),
            popover: {
                base: e,
                container: e.parentNode,
                content: e.querySelector("." + n.CONTENT),
                contentWrapper: e.querySelector("." + n.CONTENT_WRAPPER),
                tooltip: f,
                panes: Array.prototype.slice.call(e.querySelectorAll("." + n.PANE))
            },
            styles: {
                horizontalMargin: parseInt(e.getAttribute(t.CSS_CACHE.HORIZONTAL_MARGIN)),
                verticalMargin: parseInt(e.getAttribute(t.CSS_CACHE.VERTICAL_MARGIN)),
                maxHeight: parseInt(e.getAttribute(t.CSS_CACHE.MAX_HEIGHT)),
                maxWidth: parseFloat(e.getAttribute(t.CSS_CACHE.MAX_WIDTH))
            }
        }, l = !1, $(e).off("transitionend webkitTransitionEnd"), $(e).one("transitionend webkitTransitionEnd", function() {
            return l ? void 0 : (l = !0, $(e).trigger(i.ACTIVATED))
        }), y(e), m(), N()
    }, m = function() {
        var t;
        t = d.popover.base, t.setAttribute("aria-expanded", "true"), d.activatorInput.setAttribute("aria-expanded", "true"), $(t).prepareTransition().addClass(o.BASE.ACTIVE), d.popover.container.classList.add(o.CONTAINER.ACTIVE), $(t).trigger(i.ACTIVATING)
    }, y = function(t) {
        return $(t).on("wheel", "." + n.PANE, M)
    }, H = function(t) {
        var e;
        return (null != (e = d.popover) ? e.base : void 0) !== t ? h(t) : w()
    }, C = function() {
        return d.popover ? $(d.popover.base).closest(document).length ? void 0 : w() : void 0
    }, w = function(t) {
        var e, n, r, a, s, u, l;
        if (d.popover && (!t || t === d.popover.base)) {
            u = d.popover.base, s = d.popover.content, a = d.popover.container, e = $(u), n = !!e.closest(document).length, e.off("transitionend webkitTransitionEnd"), e.trigger(i.DEACTIVATING), l = function() {
                var t;
                return t = !1,
                    function() {
                        return t ? void 0 : (t = !0, a.classList.remove(o.CONTAINER.DEACTIVATING), s.style.width = "", $(u).trigger(i.DEACTIVATED))
                    }
            }(), n && (e.one("transitionend webkitTransitionEnd", l), e.prepareTransition()), f(), n || l(), A(u);
            for (r in d) delete d[r]
        }
    }, f = function() {
        var t;
        return t = d.popover, t.container.classList.add(o.CONTAINER.DEACTIVATING), t.container.classList.remove(o.CONTAINER.ACTIVE), t.base.classList.remove(o.BASE.ACTIVE), t.base.setAttribute("aria-expanded", "false"), d.activatorInput.setAttribute("aria-expanded", "false")
    }, A = function(t) {
        return $(t).off("wheel", "." + n.PANE, M)
    }, N = function(t) {
        var e, n, i, r, o, a, s, u, l, c;
        if (d.popover) return s = !(null != t ? t.type : void 0) || "resize" === t.type, i = {
            base: {},
            wrapper: {},
            content: {},
            tooltip: {}
        }, a = V(), E({
            rect: a
        }, i), s && d.horizontallyPosition && (r = b(), n = a.left / a.horizontallyRelativeTo.width <= .5, c = S(a.horizontallyRelativeTo.width), i.content.width = c, e = {
            offsets: r,
            width: c,
            left: n,
            rect: a
        }, d.positionAgainstEdge ? D(e, i) : R(e, i), u = a.width / 2 + r.leftFromContainerToActivator + r.leftFromActivatorToSource - i.base.left, i.tooltip.left = Math.round(u), o = d.popover.base.style, l = i.base.transformOrigin.split(" ").slice(1).join(" "), d.styles.transformOriginX = Math.round(u) + "px", i.base.transformOrigin = d.styles.transformOriginX + " " + l), requestAnimationFrame(function() {
            var t;
            return t = d.popover, null != t ? ($(t.base).css(i.base), $(t.content).css(i.content), $(t.contentWrapper).css(i.wrapper), $(t.tooltip).css(i.tooltip), s && (t.content.style.height = T() + 2), t.base.style.transformOrigin = i.base.transformOrigin) : void 0
        })
    }, D = function(t, e) {
        var n, i, r, o, a, s;
        return i = t.offsets, s = t.width, a = t.rect, n = t.left, r = a[n ? "right" : "left"] + a.width / 2 - d.styles.horizontalMargin, o = n ? 0 : a.width - s, o += i.leftFromContainerToActivator, e.base.left = s > r ? n ? o - (s - r) - i.leftFromActivatorToSource : o + (s - r) : n ? o - i.leftFromActivatorToSource : o - i.rightFromActivatorToSource, e.base.left = Math.round(e.base.left + i.leftFromActivatorToSource)
    }, R = function(t, e) {
        var n, i, r, o, a;
        return i = t.offsets, a = t.width, r = t.rect, n = t.left, o = .5 * a + d.styles.horizontalMargin, e.base.left = n && r.left < o ? r.width / 2 + d.styles.horizontalMargin - r.left : !n && r.right < o ? r.width / 2 - a + r.right - d.styles.horizontalMargin : r.width / 2 - a / 2, e.base.left = Math.round(e.base.left + i.leftFromContainerToActivator + i.leftFromActivatorToSource)
    }, b = function() {
        var t, e, n;
        return t = d.activator.getBoundingClientRect(), e = d.source === d.activator, n = e ? t : d.source.getBoundingClientRect(), {
            leftFromContainerToActivator: t.left - d.container.getBoundingClientRect().left,
            leftFromActivatorToSource: n.left - t.left,
            rightFromActivatorToSource: n.right - t.right
        }
    }, V = function() {
        var t, e, n, i, r;
        return n = d.source.getBoundingClientRect(), t = d.horizontallyRelativeTo.getBoundingClientRect(), r = d.verticallyRelativeTo.getBoundingClientRect(), i = n.top + .5 * n.height, e = n.left + .5 * n.width, e -= t.left, {
            height: n.height,
            width: n.width,
            top: i - z(),
            bottom: window.innerHeight - i,
            left: e,
            right: t.width - e,
            horizontallyRelativeTo: t,
            verticallyRelativeTo: r
        }
    }, T = function() {
        var t;
        return t = function(t, e) {
                return t + e.scrollHeight
            },
            function() {
                return d.popover.panes.reduce(t, 0)
            }
    }(), S = function(t) {
        var e, n;
        return d.styles.contentWidth || (e = d.popover.content, e.style.whiteSpace = "nowrap", d.styles.contentWidth = e.offsetWidth + 2, e.style.whiteSpace = ""), n = t - 2 * d.styles.horizontalMargin, Math.min(n, d.styles.maxWidth, d.styles.contentWidth)
    }, E = function(t, e) {
        var n, i, o, a, s;
        return n = d.popover.base.offsetHeight + 2 * d.styles.verticalMargin, s = t.rect, a = s.verticallyRelativeTo.top + s.verticallyRelativeTo.height - (s.top + s.height), o = window.scrollY + s.top - s.verticallyRelativeTo.top, i = d.preferredPosition === r.BOTTOM ? s.bottom < n && s.top > s.bottom || n > a && o > n ? r.TOP : r.BOTTOM : s.top < n && s.bottom > s.top || n > o && a > n ? r.BOTTOM : r.TOP, F(i, s, e)
    }, F = function(t, e, n) {
        var i, s, u;
        return u = t === r.TOP, i = d.horizontallyPosition ? 0 : "50%", n.base.transformOrigin = u ? (d.styles.transformOriginX || i) + " calc(100% + " + a + "px)" : (d.styles.transformOriginX || i) + " -" + a + "px", t !== d.position && (d.position = t, d.popover.base.classList[u ? "remove" : "add"](o.BASE.POSITIONED_BELOW), d.popover.base.classList[u ? "add" : "remove"](o.BASE.POSITIONED_ABOVE)), s = e[t] - e.height / 2 - 2 * d.styles.verticalMargin, n.content.maxHeight = Math.min(s, d.styles.maxHeight)
    }, z = function() {
        var t, e;
        return t = e = 0, $(document).on("ready page:load", function() {
                var n;
                return t = (null != (n = document.querySelector(".page .header-row")) ? n.offsetHeight : void 0) || 0, _.defer(function() {
                    var t;
                    return e = (null != (t = document.querySelector("#turbo")) ? t.offsetHeight : void 0) || 0
                })
            }),
            function() {
                return e + t
            }
    }(), L = function() {
        var t, e;
        return t = 0, e = null,
            function(i) {
                var r, o, a, s, l, c, p, m, f;
                if (o = (l = "function" == typeof(a = i.target).getAttribute ? a.getAttribute("for") : void 0) ? (c = !0, $("#" + l)) : $(i.target), !o.closest("[disabled]").length && !o.closest("." + n.BASE).length) {
                    if (r = o.closest("." + n.CONTAINER), r.length) {
                        if (c) return;
                        if (s = r[0], m = Date.now(), e === s && 500 > m - t) return;
                        if (t = m, e = s, i.preventDefault(), p = r.children("." + n.BASE)[0], p.classList.contains(u.BASE.JS_ACTIVATED)) return;
                        return p.classList.contains(u.BASE.NO_FOCUS) ? w() : "focusin" === i.type ? h(p) : H(p)
                    }
                    return (null != (f = d.popover) ? f.base.classList.contains(u.BASE.JS_ACTIVATED) : void 0) ? void 0 : w()
                }
            }
    }(), k = function(t) {
        var e, i, r, o, a;
        if (i = $(t.target).closest("." + n.BASE).length, i && t.which === Sheel.Keycodes.ESCAPE) d.activator.focus(), w(), t.preventDefault();
        else {
            if (e = $(t.currentTarget).children("." + n.BASE)[0], i || e.classList.contains(u.BASE.JS_ACTIVATED)) return;
            (r = t.which) !== Sheel.Keycodes.ENTER && r !== Sheel.Keycodes.SPACE || "input" === (null != (o = t.target) && null != (a = o.tagName) ? a.toLowerCase() : void 0) ? t.which === Sheel.Keycodes.ESCAPE && (w(), t.preventDefault()) : (H(e), t.preventDefault())
        }
    }, M = function(t) {
        var e, n, i, r, o, a, s, u, l, c;
        i = t.currentTarget, e = t.originalEvent.deltaY, c = 0 > e, o = [i.offsetHeight, i.scrollHeight, i.scrollTop], n = o[0], a = o[1], s = o[2], r = function() {
            return t.stopPropagation(), t.preventDefault()
        }, u = !c && e > a - n - s, l = c && -e > s, u && (i.scrollTop = a, r()), l && (i.scrollTop = 0, r())
    }, B = {
        activeCache: d,
        a11yPopovers: c,
        cachePopoverCSSProperties: v,
        baseFontSize: g,
        calculatePixelDimension: x,
        activate: h,
        applyActivationMarkup: m,
        attachActiveEventListeners: y,
        toggle: H,
        deactivate: w,
        applyDeactivationMarkup: f,
        detachActiveEventListeners: A,
        positionPopover: N,
        horizontallyPositionWithCenterAlignment: R,
        horizontallyPositionWithEdgeAlignment: D,
        calculateHorizontalOffsets: b,
        spaceDetailsForActivePopover: V,
        calculateMaxWidth: S,
        determineVerticalPositioning: E,
        topSpaceReservedForHeader: z,
        popoverFocus: L,
        popoverKeydown: k,
        popoverPaneScroll: M
    }, P = function() {
        var t, e, i, r, o;
        for (r = document.getElementsByClassName(n.BASE), o = [], t = 0, e = r.length; e > t; t++) i = r[t], o.push(s(i));
        return o
    }, $(document).on("ready page:load", P).on("page:load", C).on("click focusin", L).on("keydown", "." + n.CONTAINER, k), $(window).on("resize scroll", _.debounce(N, 50, {
        leading: !0
    }))
}.call(this),
function() {
    var t, e, n;
    e = 550, t = 650, n = 905, Sheel.ElementQueries.add("layout-content", "layout-content--single-column", {
        max: t
    }), Sheel.ElementQueries.add("draw-grid--outer-grid", "draw-grid--single-column", {
        max: t
    }), Sheel.ElementQueries.add("draw-grid--inner-grid", "draw-grid--single-column", {
        max: 400
    }), Sheel.ElementQueries.add("table-wrapper", "table-wrapper--scrollable", {
        max: 700
    }), Sheel.ElementQueries.add("page", "page--condense-spacing", {
        max: e
    }), Sheel.ElementQueries.add("table-filter-container", "table-filter-container--condensed", {
        max: 550
    }), Sheel.ElementQueries.add("draw-grid--channel-grid", "draw-grid--channel-single-column", {
        max: 700
    }), Sheel.ElementQueries.add("draw-grid--channel-cell", "draw-grid--channel-single-column", {
        max: 780
    }), Sheel.ElementQueries.add("individual-variant", "individual-variant--condensed", {
        max: 700
    }), Sheel.ElementQueries.add("page--with-sidebar", "page--single-column", {
        max: n
    }), Sheel.ElementQueries.add("ui-banner", "ui-banner--default-vertical-spacing", {
        min: e + 1
    }), Sheel.ElementQueries.add("ui-banner", "ui-banner--default-horizontal-spacing", {
        min: t + 1
    }), Sheel.ElementQueries.add("draw-grid--outer-grid-3", "draw-grid--single-column", {
        max: 870
    }), Sheel.ElementQueries.add("draw-table--line-items", "draw-table--condensed", {
        max: 480
    }), Sheel.ElementQueries.add("draw-table--condense", "draw-table--condensed", {
        max: 680
    }), Sheel.ElementQueries.add("empty-state__grid", "empty-state__grid--always-wrapped", {
        min: 707
    }), Sheel.ElementQueries.add("empty-state__grid", "empty-state__grid--quantity-queries", {
        min: 1040
    }), Sheel.ElementQueries.add("empty-state__wrappable", "empty-state__wrappable--left-aligned", {
        min: 455
    }), Sheel.ElementQueries.add("app-grid", "app-grid--single-column", {
        max: 640
    }), Sheel.ElementQueries.add("app-grid", "app-grid--single-row", {
        min: 1300
    })
}.call(this),
function() {
    null == window.TurboGraft && (window.TurboGraft = {
        handlers: {}
    }), TurboGraft.tgAttribute = function(t) {
        var e;
        return e = "tg-" === t.slice(0, 3) ? "data-" + t : "data-tg-" + t
    }, TurboGraft.getTGAttribute = function(t, e) {
        var n;
        return n = TurboGraft.tgAttribute(e), t.getAttribute(n) || t.getAttribute(e)
    }, TurboGraft.removeTGAttribute = function(t, e) {
        var n;
        return n = TurboGraft.tgAttribute(e), t.removeAttribute(n), t.removeAttribute(e)
    }, TurboGraft.hasTGAttribute = function(t, e) {
        var n;
        return n = TurboGraft.tgAttribute(e), null != t.getAttribute(n) || null != t.getAttribute(e)
    }, TurboGraft.querySelectorAllTGAttribute = function(t, e, n) {
        var i;
        return null == n && (n = null), i = TurboGraft.tgAttribute(e), n ? t.querySelectorAll("[" + i + "=" + n + "], [" + e + "=" + n + "]") : t.querySelectorAll("[" + i + "], [" + e + "]")
    }
}.call(this),
function() {
    window.Click = function() {
        function t(t) {
            this.event = t, this.event.defaultPrevented || (this._extractLink(), this._validForTurbolinks() && (Turbolinks.visit(this.link.href), this.event.preventDefault()))
        }
        return t.installHandlerLast = function(e) {
            return e.defaultPrevented ? void 0 : (document.removeEventListener("click", t.handle, !1), document.addEventListener("click", t.handle, !1))
        }, t.handle = function(e) {
            return new t(e)
        }, t.prototype._extractLink = function() {
            var t;
            for (t = this.event.target; t.parentNode && "A" !== t.nodeName;) t = t.parentNode;
            return "A" === t.nodeName && 0 !== t.href.length ? this.link = new Link(t) : void 0
        }, t.prototype._validForTurbolinks = function() {
            return null != this.link && !(this.link.shouldIgnore() || this._nonStandardClick())
        }, t.prototype._nonStandardClick = function() {
            return this.event.which > 1 || this.event.metaKey || this.event.ctrlKey || this.event.shiftKey || this.event.altKey
        }, t
    }()
}.call(this),
function() {
    window.ComponentUrl = function() {
        function t(e) {
            return this.original = null != e ? e : document.location.href, this.original.constructor === t ? this.original : void this._parse()
        }
        return t.prototype.withoutHash = function() {
            return this.href.replace(this.hash, "")
        }, t.prototype.withoutHashForIE10compatibility = function() {
            return this.withoutHash()
        }, t.prototype.hasNoHash = function() {
            return 0 === this.hash.length
        }, t.prototype._parse = function() {
            var t;
            return (null != this.link ? this.link : this.link = document.createElement("a")).href = this.original, t = this.link, this.href = t.href, this.protocol = t.protocol, this.host = t.host, this.hostname = t.hostname, this.port = t.port, this.pathname = t.pathname, this.search = t.search, this.hash = t.hash, this.origin = [this.protocol, "//", this.hostname].join(""), 0 !== this.port.length && (this.origin += ":" + this.port), this.relative = [this.pathname, this.search, this.hash].join(""), this.absolute = this.href
        }, t
    }()
}.call(this),
function() {
    window.CSRFToken = function() {
        function t() {}
        return t.get = function(t) {
            var e;
            return null == t && (t = document), {
                node: e = t.querySelector('meta[name="csrf-token"]'),
                token: null != e && "function" == typeof e.getAttribute ? e.getAttribute("content") : void 0
            }
        }, t.update = function(t) {
            var e;
            return e = this.get(), null != e.token && null != t && e.token !== t ? e.node.setAttribute("content", t) : void 0
        }, t
    }()
}.call(this),
function() {
    var t, e, n;
    window.Page || (window.Page = {}), Page.visit = function(t, e) {
        return null == e && (e = {}), e.reload ? window.location = t : Turbolinks.visit(t)
    }, Page.refresh = function(t, e) {
        var n, i, r;
        return null == t && (t = {}), n = t.url ? t.url : t.queryParams ? (i = $.param(t.queryParams), i ? i = "?" + i : void 0, location.pathname + i) : location.href, t.response ? (t.partialReplace = !0, t.onLoadFunction = e, r = t.response, delete t.response, Turbolinks.loadPage(null, r, t)) : (t.partialReplace = !0, e && (t.callback = e), Turbolinks.visit(n, t))
    }, Page.open = function() {
        return window.open.apply(window, arguments)
    }, n = [], Page.onReplace = function(t, i) {
        if (!t || !i) throw new Error("Page.onReplace: Node and callback must both be specified");
        if (!e(i)) throw new Error("Page.onReplace: Callback must be a function");
        return n.push({
            node: t,
            callback: i
        })
    }, e = function(t) {
        return !!(t && t.constructor && t.call && t.apply)
    }, t = function(t, e) {
        return t.contains ? t.contains(e) : !!(t === e || t.compareDocumentPosition(e) & Node.DOCUMENT_POSITION_CONTAINED_BY)
    }, document.addEventListener("page:before-partial-replace", function(e) {
        var i, r, o, a, s, u, l, c, h;
        for (c = e.data, h = [], o = 0, s = n.length; s > o; o++) {
            for (i = n[o], r = !1, a = 0, u = c.length; u > a; a++)
                if (l = c[a], t(l, i.node)) {
                    i.callback(), r = !0;
                    break
                }
            r || h.push(i)
        }
        return n = h
    }), document.addEventListener("page:before-replace", function(t) {
        var e, i, r;
        for (i = 0, r = n.length; r > i; i++) e = n[i], e.callback();
        return n = []
    })
}.call(this),function() {
    var t, e, n, i, r, o, a, s, u, l, c, h, p, d, m = function(t, e) {
        return function() {
            return t.apply(e, arguments)
        }
    }, f = [].indexOf || function(t) {
        for (var e = 0, n = this.length; n > e; e++)
            if (e in this && this[e] === t)
                return e;
        return - 1
    };
    o = "draw-tab", e = "draw-tab--disclosure", a = "draw-tab__list", i = "draw-tab__list--full", n = "draw-tab__list--vertical", r = "draw-tab__panel", h = "draw-tab--is-active", c = "draw-tab__panel--is-active", l = "is-visible", p = "draw-tab--is-in-disclosure-dropdown", u = "nextTabActivated", s = 52, t = null, $(document).on("ready page:load", function() {
        return t = $("." + a + ":not(." + n + ")").each(function() {
            return new Sheel.NextTabs(this)
        })
    }), d = function() {
        return t.triggerHandler("resize")
    }, $(function() {
        return $(window).on("resize", _.debounce(d, 50, {
            leading: !0
        }))
    }), Sheel.NextTabs = function() {
        function t(t) {
            var i, a, s, u, l;
            this.node = t, this._onTabFocus = m(this._onTabFocus, this), this._onTabBlur = m(this._onTabBlur, this), this._onTabKeydown = m(this._onTabKeydown, this), this._onTabClick = m(this._onTabClick, this), this._onTabTouch = m(this._onTabTouch, this), this.overflowTabs = m(this.overflowTabs, this), a = this.$tablist = $(this.node), i = a.find("." + e), this.$tabs = s = this.$tablist.find("." + o + ":not(." + e + ")"), this.tabs = l = s.toArray(), this._tabWidths = u = l.map(function(t) {
                return t.offsetWidth
            }), this._totalScrollWidth = u.reduce(function(t, e) {
                return t + e
            }), this.$panels = $("#" + $(l[0]).attr("aria-controls")).siblings("." + r).andSelf(), this.$disclosureTab = i, this.$disclosureTablist = i.parent().find("." + n), this.overflowTabs(), this._forceActiveTabToBeVisible(), a.on("touchstart." + o, "." + o, this._onTabTouch), a.on("click." + o, "." + o, this._onTabClick), a.on("keydown." + o, "." + o, this._onTabKeydown), a.on("blur." + o, "." + o, this._onTabBlur), a.on("focus." + o, "." + o, this._onTabFocus), a.on("resize." + o, this.overflowTabs)
        }
        return t.prototype.findActiveTab = function() {
            return this.tabs.filter(function(t) {
                return t.classList.contains(h)
            })[0]
        }, t.prototype.activateTab = function(t) {
            var e, n, i;
            return t.hasClass(h)?!1 : (this.focusTab(t), this.$tabs.attr("aria-selected", "false").removeClass(h), t.attr("aria-selected", "true").addClass(h), this.$panels.attr("aria-hidden", "true").removeClass(c), n = this.matchingPanel(t).attr("aria-hidden", "false").addClass(c), e = n.children().first().attr("tabindex", 0), this._forceActiveTabToBeVisible(t), i = t.attr("href"), i && 0 !== i.indexOf("#") || e.focus(), t.trigger(u))
        }, t.prototype.focusTab = function(t) {
            var e, n;
            return n = t.hasClass(p), e = this.$disclosureTablist.closest(".dropdown").hasClass(l), (n&&!e || e&&!n) && Sheel.Dropdown.trigger(this.$disclosureTab), this.$tabs.attr("tabindex", - 1), t.attr("tabindex", 0), t.focus()
        }, t.prototype.overflowTabs = function() {
            var t, e, n, i, r;
            return t = this.$tablist[0].offsetWidth, null == this._lastWidth && (this._lastWidth = t), i = this._disclosureWidth(), e = this._totalScrollWidth - t, n = e + i, r = this.$disclosureTablist.length > 0, (t <= this._lastWidth && r && n > 0 ||!r && e > 0) && this._hideTabsToFit(n), t > this._lastWidth && this._showTabsToFit(n), this._lastWidth = t
        }, t.prototype._disclosureWidth = function() {
            var t;
            return (null != (t = this.$disclosureTab[0]) ? t.offsetWidth : void 0) || s
        }, t.prototype._hideTabsToFit = function(t) {
            var e, n, r, o, a, s, u, l;
            for (this.$tablist.addClass(i), s = this.tabs.slice().reverse(), n = 0, o = s.length, a = []; n < s.length && t > 0;)
                u = s[n], u.classList.contains(h) || u.classList.contains(p) || (l = u.offsetWidth, e = $(u), e.addClass(p), e.parent().prependTo(this.$disclosureTablist), r = void 0, null != this._lastTabIndexInDisclosure ? (r = this._lastTabIndexInDisclosure - 1, this._lastTabIndexInDisclosure -= 1) : (r = o - 1, this._lastTabIndexInDisclosure = o - 1), this._moveTabToIndex(o - n - 1, r), this._totalScrollWidth -= l, t -= l), a.push(n += 1);
            return a
        }, t.prototype._showTabsToFit = function(t) {
            var e, n, r, o, a, s, u, l, c, d, m, f;
            for (f = this.tabs.slice(), d = f.length, r = this.findActiveTab(), e = o = void 0, (s = this.tabs.indexOf(r) + 1) < this.tabs.length && this.tabs[this.tabs.indexOf(r) + 1].classList.contains(p) ? (e = $(r).parent(), o = this.tabs.indexOf(r)) : (e = this.$disclosureTab.parent(), o = this._lastTabIndexInDisclosure), a = 0, l = []; d > a && 0 > t;) {
                if (c = f[a], m = this._tabWidths[a], c.classList.contains(p)&&!c.classList.contains(h)) {
                    if (n = $(c), u = a + 1 === d, u && (t -= this._disclosureWidth()), !( - t > m))
                        break;
                    n.removeClass(p), n.parent().insertBefore(e), this._moveTabToIndex(a, o), this._lastTabIndexInDisclosure += 1, this._totalScrollWidth += m, t += m, u && this.$tablist.removeClass(i)
                }
                l.push(a += 1)
            }
            return l
        }, t.prototype._forceActiveTabToBeVisible = function(t) {
            var e;
            return null == t && (t = $(this.findActiveTab())), t.hasClass(p) ? (t.removeClass(p), e = this._lastTabIndexInDisclosure - 1, this._moveTabToIndex(this.tabs.indexOf(t[0]), e), this._lastTabIndexInDisclosure += 1, t.parent().insertBefore(this.$disclosureTab.parent()), this._totalScrollWidth += t[0].offsetWidth, this.overflowTabs()) : void 0
        }, t.prototype._moveTabToIndex = function(t, e) {
            var n;
            if (t !== e)
                return n = this.tabs.splice(t, 1)[0], this.tabs.splice(e, 0, n), n = this._tabWidths.splice(t, 1)[0], this._tabWidths.splice(e, 0, n)
        }, t.prototype.matchingPanel = function(t) {
            return $("#" + t.attr("aria-controls"))
        }, t.prototype._onTabTouch = function(t) {
            var n;
            return n = $(t.currentTarget), n.hasClass(e) ? (Sheel.Dropdown.trigger(n), t.stopPropagation()) : void 0
        }, t.prototype._onTabClick = function(t) {
            var n, i;
            1 === t.which && (n = $(t.currentTarget), n.hasClass(e) || (i = n.attr("href"), i && 0 === i.indexOf("#") && t.preventDefault(), t.metaKey || t.ctrlKey || this.activateTab(n)))
        }, t.prototype._onTabKeydown = function(t) {
            var e, n, i, r;
            return r = [Sheel.Keycodes.LEFT, Sheel.Keycodes.RIGHT, Sheel.Keycodes.UP, Sheel.Keycodes.DOWN, Sheel.Keycodes.ENTER], i = t.which, f.call(r, i) < 0 ? void 0 : (t.preventDefault(), e = $(t.currentTarget), n = this.tabs.indexOf(e[0]), t.which === Sheel.Keycodes.RIGHT || t.which === Sheel.Keycodes.DOWN ? void(n === this.tabs.length - 1 ? this.focusTab($(this.tabs[0])) : this.focusTab($(this.tabs[n + 1]))) : t.which === Sheel.Keycodes.LEFT || t.which === Sheel.Keycodes.UP ? void(0 === n ? this.focusTab($(this.tabs[this.tabs.length - 1])) : this.focusTab($(this.tabs[n - 1]))) : void(t.which === Sheel.Keycodes.ENTER && e[0].click()))
        }, t.prototype._onTabBlur = function(t) {
            var n;
            n = $(t.currentTarget), n.hasClass(e) || (this.$_focusedTab = null, setTimeout(function(t) {
                return function() {
                    var e;
                    if (null == t.$_focusedTab)
                        return t.$disclosureTab.siblings(".dropdown").hasClass(l) && Sheel.Dropdown.trigger(t.$disclosureTab), t.$tabs.attr("tabindex", - 1), (e = $(t.findActiveTab())).hasClass(p) ? t.$disclosureTab.attr("tabindex", 0) : e.attr("tabindex", 0)
                }
            }(this), 50))
        }, t.prototype._onTabFocus = function(t) {
            var n;
            this.$_focusedTab = n = $(t.currentTarget), n.hasClass(e) && (n.siblings(".dropdown").hasClass("is-visible") || Sheel.Dropdown.trigger(n), this.$disclosureTablist.find("." + h).focus(), n.attr("tabindex", - 1))
        }, t
    }(), Sheel.NextTabs.EVENT_TAB_ACTIVATED = u
}.call(this), function() {
    var t, e, n;
    $(document).on("click", function(t) {
        var e, n, i;
        return n = $(t.target), n.parents('[class*="ui-"]').length&&!n.closest(".dropdown-container, .draw-dropdown__container").length || n.closest(".draw-tab--disclosure").length ? void 0 : (i = n.closest("a"), (e = n.closest("[data-dropdown]")).length ? (t.preventDefault(), Sheel.Dropdown.trigger(e)) : i.length&&!i.hasClass("js-keep-open") ||!n.closest(".dropdown, .draw-dropdown").length ? Sheel.Dropdown.hide() : void 0)
    }), n = function(e) {
        var n, i;
        if (!e.prop("disabled"))
            return e.parent().addClass("active"), n = t(e), n.removeClass("display-from-bottom"), e.trigger("dropdown:show"), i = $(window), n.length && i.scrollTop() + i.height() - n.offset().top < n.siblings(".dropdown, .draw-dropdown").height() ? n.addClass("display-from-bottom") : void 0
    }, t = function(t) {
        var e, n;
        return n = t.attr("data-dropdown"), e = $(n, t)
    }, e = function(e) {
        var n, i;
        return i = e.parent(), i.hasClass("active") ? (n = t(e), n.removeClass("display-from-bottom"), i.removeClass("active"), e.trigger("dropdown:hide")) : void 0
    }, Sheel.Dropdown = {
        trigger: function(t) {
            return t.parent().hasClass("active") ? t.hasClass("js-keep-open") ? void 0 : e(t) : (t.parents(".dropdown, .draw-dropdown").length || this.hide(), n(t))
        },
        hide: function() {
            return $("[data-dropdown]").each(function() {
                return e($(this))
            })
        }
    }
}.call(this),
function() {
    var t, e, n, i, r, o, a, s, u, l, c, h, p = [].slice,
        d = [].indexOf || function(t) {
            for (var e = 0, n = this.length; n > e; e++)
                if (e in this && this[e] === t) return e;
            return -1
        };
    h = null, r = function() {
        return document.addEventListener("DOMContentLoaded", function() {
            return triggerEvent("page:change"), triggerEvent("page:update")
        }, !0)
    }, o = function() {
        return "undefined" != typeof jQuery ? jQuery(document).on("ajaxSuccess", function(t, e, n) {
            return jQuery.trim(e.responseText) ? triggerEvent("page:update") : void 0
        }) : void 0
    }, i = void 0 !== window.history.state || navigator.userAgent.match(/Firefox\/2[6|7]/), e = window.history && window.history.pushState && window.history.replaceState && i, window.triggerEvent = function(t, e) {
        var n;
        return n = document.createEvent("Events"), e && (n.data = e), n.initEvent(t, !0, !0), document.dispatchEvent(n)
    }, window.triggerEventFor = function(t, e, n) {
        var i;
        return i = document.createEvent("Events"), n && (i.data = n), i.initEvent(t, !0, !0), e.dispatchEvent(i)
    }, a = function(t) {
        var e, n;
        return n = (null != (e = document.cookie.match(new RegExp(t + "=(\\w+)"))) ? e[1].toUpperCase() : void 0) || "", document.cookie = t + "=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/", n
    }, c = "GET" === (s = a("request_method")) || "" === s, n = e && c, t = document.addEventListener && document.createEvent, t && (r(), o()), l = function(t, e) {
        var n;
        return n = e.parentNode.replaceChild(t, e), triggerEvent("page:after-node-removed", n)
    }, u = function(t) {
        var e;
        return e = t.parentNode.removeChild(t), triggerEvent("page:after-node-removed", e)
    }, window.Turbolinks = function() {
        function t() {}
        var e, i, r, o, a, s, c, m, f, y, _, g, v, b, S, x, T, C, w, A, E, R, D, P, I, $, L, O, k;
        return o = null, a = null, x = null, E = null, y = function(t, e) {
            return null == e && (e = {}), T(t) ? void 0 : (t = new ComponentUrl(t), $(), null == e.partialReplace && (e.partialReplace = !1), null == e.onlyKeys && (e.onlyKeys = []), e.onLoadFunction = function() {
                return e.onlyKeys.length || O(), "function" == typeof e.callback ? e.callback() : void 0
            }, _(t, e))
        }, t.pushState = function(t, e, n) {
            return window.history.pushState(t, e, n)
        }, t.replaceState = function(t, e, n) {
            return window.history.replaceState(t, e, n)
        }, _ = function(e, n) {
            var i, r, o;
            triggerEvent("page:fetch", {
                url: e.absolute
            }), null != h && h.abort(), h = new XMLHttpRequest, h.open("GET", e.withoutHashForIE10compatibility(), !0), h.setRequestHeader("Accept", "text/html, application/xhtml+xml, application/xml"), h.setRequestHeader("X-XHR-Referer", E), null == n.headers && (n.headers = {}), r = n.headers;
            for (i in r) o = r[i], h.setRequestHeader(i, o);
            h.onload = function() {
                return h.status >= 500 ? document.location.href = e.absolute : t.loadPage(e, h, n)
            }, h.onloadend = function() {
                return h = null
            }, h.onerror = function() {
                return document.location.href = e.absolute
            }, h.send()
        }, t.loadPage = function(t, e, n) {
            var i, o;
            null == n && (n = {}), triggerEvent("page:receive"), null == n.updatePushState && (n.updatePushState = !0), (i = w(e, n.partialReplace)) ? (n.updatePushState && R(t), o = r.apply(null, p.call(f(i)).concat([n])), n.updatePushState && D(e), triggerEvent("page:load", o), "function" == typeof n.onLoadFunction && n.onLoadFunction()) : document.location.href = t.absolute
        }, r = function(t, n, i, r, o) {
            var u, c;
            return null == o && (o = {}), t && (document.title = t), null == o.onlyKeys && (o.onlyKeys = []), null == o.exceptKeys && (o.exceptKeys = []), o.onlyKeys.length ? (c = [].concat(v(), g(o.onlyKeys)), u = I(c, n), e(u) && k(), u) : (I(v(), n), C(n), o.exceptKeys.length ? P(o.exceptKeys, n) : s(n), triggerEvent("page:before-replace"), l(n, document.body), null != i && CSRFToken.update(i), k(), r && m(), a = window.history.state, triggerEvent("page:change"), triggerEvent("page:update"), void 0)
        }, g = function(t) {
            var e, n, i, r, o, a, s, u;
            for (a = [], e = 0, r = t.length; r > e; e++)
                for (i = t[e], u = TurboGraft.querySelectorAllTGAttribute(document, "refresh", i), n = 0, o = u.length; o > n; n++) s = u[n], a.push(s);
            return a
        }, v = function() {
            var t, e, n, i, r;
            for (n = [], r = TurboGraft.querySelectorAllTGAttribute(document, "refresh-always"), t = 0, e = r.length; e > t; t++) i = r[t], n.push(i);
            return n
        }, e = function(t) {
            var e, n, i;
            for (e = 0, n = t.length; n > e; e++)
                if (i = t[e], i.querySelectorAll("input[autofocus], textarea[autofocus]").length > 0) return !0;
            return !1
        }, k = function() {
            var t, e;
            return t = (e = document.querySelectorAll("input[autofocus], textarea[autofocus]"))[e.length - 1], t && document.activeElement !== t ? t.focus() : void 0
        }, s = function(t) {
            var e, n, i, r;
            for (r = TurboGraft.querySelectorAllTGAttribute(t, "refresh-never"), e = 0, n = r.length; n > e; e++) i = r[e], u(i)
        }, I = function(t, e) {
            var n, i, r, o, a, s, h;
            for (triggerEvent("page:before-partial-replace", t), s = function(e) {
                    var n, i, r;
                    for (n = 0, i = t.length; i > n; n++)
                        if (r = t[n], e !== r && r.contains(e)) return !0;
                    return !1
                }, h = [], i = 0, r = t.length; r > i; i++)
                if (n = t[i], !s(n)) {
                    if (!(a = n.getAttribute("id"))) throw new Error("Turbolinks refresh: Refresh key elements must have an id.");
                    (o = e.querySelector("#" + a)) ? (o = o.cloneNode(!0), l(o, n), "SCRIPT" === o.nodeName && "false" !== o.getAttribute("data-turbolinks-eval") ? c(o) : h.push(o)) : null === TurboGraft.getTGAttribute(n, "refresh-always") && u(n)
                }
            return h
        }, S = function(t, e) {
            var n, i, r, o, a, s;
            for (s = [], i = 0, r = e.length; r > i; i++) {
                if (n = e[i], !(o = n.getAttribute("id"))) throw new Error("TurboGraft refresh: Kept nodes must have an id.");
                (a = t.querySelector("#" + o)) ? s.push(l(n, a)): s.push(void 0)
            }
            return s
        }, C = function(t) {
            var e, n, i, r, o;
            for (e = [], o = TurboGraft.querySelectorAllTGAttribute(document, "tg-static"), n = 0, i = o.length; i > n; n++) r = o[n], e.push(r);
            S(t, e)
        }, P = function(t, e) {
            var n, i, r, o, a, s, u, l;
            for (n = [], i = 0, a = t.length; a > i; i++)
                for (o = t[i], l = TurboGraft.querySelectorAllTGAttribute(document, "refresh", o), r = 0, s = l.length; s > r; r++) u = l[r], n.push(u);
            S(e, n)
        }, m = function() {
            var t, e, n, i, r;
            for (r = Array.prototype.slice.call(document.body.querySelectorAll('script:not([data-turbolinks-eval="false"])')), t = 0, e = r.length; e > t; t++) i = r[t], ("" === (n = i.type) || "text/javascript" === n) && c(i)
        }, c = function(t) {
            var e, n, i, r, o, a, s;
            for (n = document.createElement("script"), s = t.attributes, i = 0, r = s.length; r > i; i++) e = s[i], n.setAttribute(e.name, e.value);
            n.appendChild(document.createTextNode(t.innerHTML)), a = t.parentNode, o = t.nextSibling, a.removeChild(t), a.insertBefore(n, o)
        }, L = function(t) {
            return t.innerHTML = t.innerHTML.replace(/<noscript[\S\s]*?<\/noscript>/gi, ""), t
        }, R = function(e) {
            (e = new ComponentUrl(e)).absolute !== E && t.pushState({
                turbolinks: !0,
                url: e.absolute
            }, "", e.absolute)
        }, D = function(e) {
            var n, i;
            (n = e.getResponseHeader("X-XHR-Redirected-To")) && (n = new ComponentUrl(n), i = n.hasNoHash() ? document.location.hash : "", t.replaceState(a, "", n.href + i))
        }, $ = function() {
            return E = document.location.href
        }, t.rememberCurrentUrl = function() {
            return t.replaceState({
                turbolinks: !0,
                url: document.location.href
            }, "", document.location.href)
        }, t.rememberCurrentState = function() {
            return a = window.history.state
        }, A = function(t) {
            return window.scrollTo(t.positionX, t.positionY)
        }, O = function() {
            return document.location.hash ? document.location.href = document.location.href : window.scrollTo(0, 0)
        }, T = function(t) {
            return !triggerEvent("page:before-change", t)
        }, w = function(t, e) {
            var n, i, r, a, s, u, l;
            return null == e && (e = !1), r = function() {
                var e;
                return 422 === t.status ? !1 : 400 <= (e = t.status) && 600 > e
            }, l = function() {
                return t.getResponseHeader("Content-Type").match(/^(?:text\/html|application\/xhtml\+xml|application\/xml)(?:;|$)/)
            }, s = function(t) {
                var e, n, i, r, o;
                for (r = t.querySelector("head").childNodes, o = [], e = 0, n = r.length; n > e; e++) i = r[e], null != ("function" == typeof i.getAttribute ? i.getAttribute("data-turbolinks-track") : void 0) && o.push(i.getAttribute("src") || i.getAttribute("href"));
                return o
            }, n = function(t) {
                var e;
                return x || (x = s(document)), e = s(t), e.length !== x.length || u(e, x).length !== x.length
            }, u = function(t, e) {
                var n, i, r, o, a;
                for (t.length > e.length && (r = [e, t], t = r[0], e = r[1]), o = [], n = 0, i = t.length; i > n; n++) a = t[n], d.call(e, a) >= 0 && o.push(a);
                return o
            }, r() || !l() || (a = o(t.responseText), i = n(a), !a || i && !e) ? void 0 : a
        }, f = function(t) {
            var e;
            return e = t.querySelector("title"), [null != e ? e.textContent : void 0, L(t.querySelector("body")), CSRFToken.get(t).token, "runScripts"]
        }, b = function(e) {
            var n;
            return (null != (n = e.state) ? n.turbolinks : void 0) ? t.visit(e.target.location.href) : void 0
        }, i = function(t) {
            return setTimeout(t, 500)
        }, o = function(t) {
            var e;
            return /<(html|body)/i.test(t) ? (e = document.documentElement.cloneNode(), e.innerHTML = t) : (e = document.documentElement.cloneNode(!0), e.querySelector("body").innerHTML = t), e.head = e.querySelector("head"), e.body = e.querySelector("body"), e
        }, n ? (t.visit = y, t.rememberCurrentUrl(), t.rememberCurrentState(), document.addEventListener("click", Click.installHandlerLast, !0), i(function() {
            return window.addEventListener("popstate", b, !1)
        })) : t.visit = function(t) {
            return document.location.href = t
        }, t
    }()
}.call(this),
function() {}.call(this);
