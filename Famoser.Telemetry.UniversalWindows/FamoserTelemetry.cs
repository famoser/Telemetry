using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging;
using Famoser.Telemetry.UniversalWindows.Logger;

namespace Famoser.Telemetry.UniversalWindows
{
    public class FamoserTelemetry
    {
        private static string _url = "https://telemetry.famoser.ch";

        public static void Initialize(string applicationId)
        {
            LogHelper.Instance.OverwriteLogger(new TelemetryLogger());
        }

        public static void ConfigureUrl(string url)
        {
            _url = url;
        }
    }
}
