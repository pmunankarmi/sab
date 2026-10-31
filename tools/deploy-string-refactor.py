"""Compatibility entry point: deploy the complete current theme."""
from pathlib import Path
import runpy
runpy.run_path(str(Path(__file__).with_name('sync-local.py')))
