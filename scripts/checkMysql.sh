#!/bin/bash
curl http://127.0.0.1:3306 --output - 2>&1 | grep -o 8.0