diff --git a/.codeclimate.yml b/.codeclimate.yml
index e69de29..1935889 100644
--- a/.codeclimate.yml
+++ b/.codeclimate.yml
@@ -0,0 +1,27 @@
+---
+engines:
+  csslint:
+    enabled: true
+  duplication:
+    enabled: true
+    config:
+      languages:
+      - ruby
+      - javascript
+      - python
+      - php
+  fixme:
+    enabled: true
+  phpmd:
+    enabled: true
+ratings:
+  paths:
+  - "**.css"
+  - "**.inc"
+  - "**.js"
+  - "**.jsx"
+  - "**.module"
+  - "**.php"
+  - "**.py"
+  - "**.rb"
+exclude_paths: []
diff --git a/.csslintrc b/.csslintrc
index e69de29..aacba95 100644
--- a/.csslintrc
+++ b/.csslintrc
@@ -0,0 +1,2 @@
+--exclude-exts=.min.css
+--ignore=adjoining-classes,box-model,ids,order-alphabetical,unqualified-attributes
